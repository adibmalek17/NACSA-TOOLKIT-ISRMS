<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class RiskAssessmentLifecycleController extends Controller
{
    public function destroy(Request $request, string $assessment)
    {
        $validated = $request->validate([
            'revision' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'string'],
            'confirm_delete' => ['accepted'],
        ]);

        DB::transaction(function () use ($request, $assessment, $validated) {
            $record = DB::table('risk_assessments')
                ->join('risk_assets', 'risk_assessments.risk_asset_id', '=', 'risk_assets.id')
                ->where('risk_assessments.id', $assessment)
                ->whereIn('risk_assets.department_id', DB::table('department_memberships')
                    ->where('user_id', $request->user()->id)->select('department_id'))
                ->select('risk_assessments.*', 'risk_assets.department_id', 'risk_assets.code as asset_code')
                ->lockForUpdate()->first();
            abort_if($record === null, 404);

            $membership = DB::table('department_memberships')
                ->where('user_id', $request->user()->id)
                ->where('department_id', $record->department_id)
                ->lockForUpdate()->first();
            $allowed = $membership !== null && (
                $membership->role === 'admin'
                || ($membership->role === 'assessor' && (int) $record->assessor_id === (int) $request->user()->id)
            );
            abort_unless($allowed, 403, 'Only the department Admin or the assessment owner can delete this assessment.');
            abort_unless(
                (int) $record->revision === (int) $validated['revision'] && $record->status === $validated['status'],
                409, 'The assessment has changed. Refresh the page before deleting.'
            );

            $plans = DB::table('risk_treatments')->where('risk_assessment_id', $record->id)
                ->lockForUpdate()->get();
            // Include treatment-linked reviews explicitly, respecting restrictive foreign keys.
            $reviews = DB::table('risk_reviews')->where(function ($query) use ($record, $plans) {
                $query->where('risk_assessment_id', $record->id)
                    ->orWhereIn('risk_treatment_id', $plans->pluck('id'));
            })->lockForUpdate()->get();
            $events = DB::table('risk_audit_events')->where('risk_assessment_id', $record->id)
                ->lockForUpdate()->get();

            foreach ($events as $event) {
                $metadata = json_decode($event->metadata, true, 512, JSON_THROW_ON_ERROR);
                $metadata['deleted_assessment_id'] = (int) $record->id;
                $metadata['department_id'] = (int) $record->department_id;
                DB::table('risk_audit_events')->where('id', $event->id)->update([
                    'risk_assessment_id' => null,
                    'metadata' => json_encode($metadata, JSON_THROW_ON_ERROR),
                ]);
            }

            DB::table('risk_audit_events')->insert([
                'actor_id' => $request->user()->id,
                'risk_assessment_id' => null,
                'action' => 'assessment.deleted',
                'metadata' => json_encode([
                    'deleted_assessment_id' => (int) $record->id,
                    'department_id' => (int) $record->department_id,
                    'asset_id' => (int) $record->risk_asset_id,
                    'asset_code' => $record->asset_code,
                    'previous_status' => $record->status,
                    'revision' => (int) $record->revision,
                    'deleted_treatment_ids' => $plans->pluck('id')->all(),
                    'deleted_review_ids' => $reviews->pluck('id')->all(),
                ], JSON_THROW_ON_ERROR),
                'created_at' => now(),
            ]);
            DB::table('risk_reviews')->whereIn('id', $reviews->pluck('id'))->delete();
            DB::table('risk_treatments')->whereIn('id', $plans->pluck('id'))->delete();
            DB::table('risk_assessments')->where('id', $record->id)->delete();
        });

        return redirect()->route('assessments.index')->with(
            'success', 'Assessment and linked reviews/treatments deleted. The asset and audit events were retained.'
        );
    }
}
