<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

final class RiskReviewController extends Controller
{
    private function assessmentQuery(Request $request, string $assessment)
    {
        return DB::table('risk_assessments')
            ->join(
                'risk_assets',
                'risk_assessments.risk_asset_id',
                '=',
                'risk_assets.id'
            )
            ->where('risk_assessments.id', $assessment)
            ->whereIn(
                'risk_assets.department_id',
                DB::table('department_memberships')
                    ->where('user_id', $request->user()->id)
                    ->select('department_id')
            )
            ->select(
                'risk_assessments.*',
                'risk_assets.code as asset_code',
                'risk_assets.department_id',
                'risk_assets.deleted_at as asset_deleted_at'
            );
    }

    private function hasRole(
        Request $request,
        int $departmentId,
        array $roles
    ): bool {
        return DB::table('department_memberships')
            ->where('user_id', $request->user()->id)
            ->where('department_id', $departmentId)
            ->whereIn('role', $roles)
            ->exists();
    }

    public function show(Request $request, string $assessment)
    {
        $record = $this->assessmentQuery($request, $assessment)->first();

        abort_if($record === null, 404);

        $canSubmit = $this->hasRole(
            $request,
            (int) $record->department_id,
            ['admin', 'assessor']
        );

        $canReview = $this->hasRole(
            $request,
            (int) $record->department_id,
            ['reviewer']
        ) && (int) $record->assessor_id !== (int) $request->user()->id;

        $reviews = DB::table('risk_reviews')
        ->whereNull('risk_reviews.risk_treatment_id')
            ->leftJoin('users', 'risk_reviews.reviewer_id', '=', 'users.id')
            ->where('risk_reviews.risk_assessment_id', $record->id)
            ->select('risk_reviews.*', 'users.name as reviewer_name')
            ->orderByDesc('risk_reviews.id')
            ->get();

        return view('assessments.review', [
            'assessment' => $record,
            'canSubmit' => $canSubmit,
            'canEdit' => $this->hasRole($request, (int) $record->department_id, ['admin'])
                || ($this->hasRole($request, (int) $record->department_id, ['assessor'])
                    && (int) $record->assessor_id === (int) $request->user()->id),
            'canReview' => $canReview,
            'reviews' => $reviews,
            'residualPanels' => \App\Services\ResidualReviewPresentation::currentPlans($record, (int) $request->user()->id),
        ]);
    }

    public function submit(Request $request, string $assessment)
    {
        $validated = $request->validate([
            'revision' => ['required', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($request, $assessment, $validated) {
            $record = $this->assessmentQuery($request, $assessment)
                ->lockForUpdate()
                ->first();

            abort_if($record === null, 404);

            abort_unless(
                $this->hasRole(
                    $request,
                    (int) $record->department_id,
                    ['admin', 'assessor']
                ),
                403,
                'You cannot submit assessments in this department.'
            );

            abort_if(
                $record->asset_deleted_at !== null,
                409,
                'This asset is archived.'
            );

            abort_unless(
                $record->status === 'draft',
                409,
                'Only draft assessments can be submitted.'
            );

            abort_unless(
                (int) $record->revision === (int) $validated['revision'],
                409,
                'The assessment has changed. Refresh the page.'
            );

            DB::table('risk_assessments')
                ->where('id', $record->id)
                ->update([
                    'status' => 'submitted',
                    'updated_at' => now(),
                ]);

            DB::table('risk_audit_events')->insert([
                'actor_id' => $request->user()->id,
                'risk_assessment_id' => $record->id,
                'action' => 'assessment.submitted',
                'metadata' => json_encode([
                    'revision' => $record->revision,
                    'department_id' => $record->department_id,
                    'from_status' => 'draft',
                    'to_status' => 'submitted',
                ], JSON_THROW_ON_ERROR),
                'created_at' => now(),
            ]);
        });

        return redirect()
            ->route('assessments.review', ['assessment' => $assessment])
            ->with('success', 'Assessment submitted for review.');
    }

    public function decide(Request $request, string $assessment)
    {
        $validated = $request->validate([
            'revision' => ['required', 'integer', 'min:1'],
            'decision' => ['required', Rule::in(['verified', 'returned'])],
            'comment' => ['required', 'string', 'max:5000'],
        ]);

        DB::transaction(function () use ($request, $assessment, $validated) {
            $record = $this->assessmentQuery($request, $assessment)
                ->lockForUpdate()
                ->first();

            abort_if($record === null, 404);

            abort_unless(
                $this->hasRole(
                    $request,
                    (int) $record->department_id,
                    ['reviewer']
                ),
                403,
                'You cannot review assessments in this department.'
            );

            abort_if(
                (int) $record->assessor_id === (int) $request->user()->id,
                403,
                'You cannot review your own assessment.'
            );

            abort_if(
                $record->asset_deleted_at !== null,
                409,
                'This asset is archived.'
            );

            abort_unless(
                $record->status === 'submitted',
                409,
                'Only submitted assessments can be reviewed.'
            );

            abort_unless(
                (int) $record->revision === (int) $validated['revision'],
                409,
                'The assessment has changed. Refresh the page.'
            );

            $now = now();

            $reviewId = DB::table('risk_reviews')->insertGetId([
                'risk_assessment_id' => $record->id,
                'reviewer_id' => $request->user()->id,
                'revision' => $record->revision,
                'stage' => 'assessment',
                'decision' => $validated['decision'],
                'comment' => $validated['comment'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('risk_assessments')
                ->where('id', $record->id)
                ->update([
                    'status' => $validated['decision'],
                    'updated_at' => $now,
                ]);

            DB::table('risk_audit_events')->insert([
                'actor_id' => $request->user()->id,
                'risk_assessment_id' => $record->id,
                'action' => 'assessment.' . $validated['decision'],
                'metadata' => json_encode([
                    'review_id' => $reviewId,
                    'revision' => $record->revision,
                    'department_id' => $record->department_id,
                    'from_status' => 'submitted',
                    'to_status' => $validated['decision'],
                ], JSON_THROW_ON_ERROR),
                'created_at' => $now,
            ]);
        });

        return redirect()
            ->route('assessments.review', ['assessment' => $assessment])
            ->with('success', 'Review decision saved successfully.');
    }
}