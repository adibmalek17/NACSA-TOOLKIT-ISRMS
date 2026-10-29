<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Risk\RiskCalculator;
use App\Services\TreatmentRevision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class RiskTreatmentImplementationController extends Controller
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
            ->whereIn('risk_assets.department_id', function ($query) use ($request) {
                $query->select('department_id')
                    ->from('department_memberships')
                    ->where('user_id', $request->user()->id);
            })
            ->select(
                'risk_assessments.*',
                'risk_assets.code as asset_code',
                'risk_assets.department_id',
                'risk_assets.deleted_at as asset_deleted_at'
            );
    }

    private function canManage(Request $request, object $assessment): bool
    {
        return DB::table('department_memberships')
            ->where('user_id', $request->user()->id)
            ->where('department_id', $assessment->department_id)
            ->whereIn('role', ['admin', 'assessor'])
            ->exists();
    }

    public function show(
        Request $request,
        string $assessment,
        string $treatment
    ) {
        $record = $this->assessmentQuery($request, $assessment)->first();

        abort_if($record === null, 404);

        $plan = DB::table('risk_treatments')
            ->where('id', $treatment)
            ->where('risk_assessment_id', $record->id)
            ->first();

        abort_if($plan === null, 404);

        $record->inputs = json_decode(
            $record->inputs,
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $record->result = json_decode(
            $record->result,
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $plan->residual_inputs = $plan->residual_inputs !== null
            ? json_decode($plan->residual_inputs, true, 512, JSON_THROW_ON_ERROR)
            : null;

        $plan->residual_result = $plan->residual_result !== null
            ? json_decode($plan->residual_result, true, 512, JSON_THROW_ON_ERROR)
            : null;

        return view('assessments.implementation', [
            'assessment' => $record,
            'treatment' => $plan,
            'isCurrentTreatment' => TreatmentRevision::isCurrent($record, $plan),
            'baseline' => TreatmentRevision::baseline($record, $plan),
            'canManage' => $this->canManage($request, $record),
        ]);
    }

    public function store(
        Request $request,
        string $assessment,
        string $treatment,
        RiskCalculator $calculator
    ) {
        DB::transaction(function () use (
            $request,
            $assessment,
            $treatment,
            $calculator
        ) {
            $record = $this->assessmentQuery($request, $assessment)
                ->lockForUpdate()
                ->first();

            abort_if($record === null, 404);

            abort_unless(
                $this->canManage($request, $record),
                403,
                'You cannot record implementation in this department.'
            );

            abort_if(
                $record->asset_deleted_at !== null,
                409,
                'This asset is archived.'
            );

            abort_unless(
                $record->status === 'verified',
                409,
                'Implementation requires a verified assessment.'
            );

            $plan = DB::table('risk_treatments')
                ->where('id', $treatment)
                ->where('risk_assessment_id', $record->id)
                ->lockForUpdate()
                ->first();

            abort_if($plan === null, 404);
            abort_unless(TreatmentRevision::isCurrent($record, $plan), 409,
                'This treatment belongs to an earlier or unknown revision and is view only. Create a new plan after verifying the current assessment.');


            abort_if(
                $plan->implemented_at !== null
                    || $plan->residual_result !== null,
                409,
                'Implementation has already been recorded for this plan.'
            );

            $profile = $record->profile;
            $max = $profile === 'nacsa-5-2024' ? 5 : 3;

            $scoreKeys = array_merge(
                ['c', 'i', 'a', 'likelihood'],
                $profile === 'usim-2026'
                    ? ['operations', 'financial', 'reputation']
                    : ['loss']
            );

            $rules = [
                'revision' => ['required', 'integer', 'min:1'],
                'implementation_evidence' => [
                    'required',
                    'string',
                    'max:10000',
                ],
            ];

            foreach ($scoreKeys as $key) {
                $rules[$key] = ['required', 'integer', "between:1,$max"];
            }

            $validated = $request->validate($rules);

            abort_unless(
                (int) $record->revision === (int) $validated['revision'],
                409,
                'The assessment has changed. Refresh the page.'
            );

            $inputs = ['profile' => $profile];

            foreach ($scoreKeys as $key) {
                $inputs[$key] = (int) $validated[$key];
            }

            // Calculate on the server using the existing assessment profile.
            $result = $calculator->calculate($inputs);

            abort_unless(
                $result['version'] === $record->engine_version,
                409,
                'The scoring engine version has changed. Review version compatibility before recording residual risk.'
            );

            $now = now();

            DB::table('risk_treatments')
                ->where('id', $plan->id)
                ->update([
                    'implementation_evidence' =>
                        $validated['implementation_evidence'],
                    'residual_inputs' =>
                        json_encode($inputs, JSON_THROW_ON_ERROR),
                    'residual_result' =>
                        json_encode($result, JSON_THROW_ON_ERROR),
                    'implemented_at' => $now,
                    'updated_at' => $now,
                ]);

            DB::table('risk_audit_events')->insert([
                'actor_id' => $request->user()->id,
                'risk_assessment_id' => $record->id,
                'action' => 'treatment.implemented',
                'metadata' => json_encode([
                    'treatment_id' => $plan->id,
                    'department_id' => $record->department_id,
                    'assessment_revision' => $record->revision,
                    'profile' => $profile,
                    'engine_version' => $result['version'],
                    'residual_score' => $result['score'],
                    'residual_level' => $result['level'],
                ], JSON_THROW_ON_ERROR),
                'created_at' => $now,
            ]);
        });

        return redirect()
            ->route('assessments.implementation', [
                'assessment' => $assessment,
                'treatment' => $treatment,
            ])
            ->with(
                'success',
                'Implementation evidence and residual risk saved successfully.'
            );
    }
}