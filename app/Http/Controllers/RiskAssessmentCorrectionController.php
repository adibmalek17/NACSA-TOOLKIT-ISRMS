<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Risk\RiskCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class RiskAssessmentCorrectionController extends Controller
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

    private function ensureCanCorrect(Request $request, object $assessment): void
    {
        $allowed = DB::table('department_memberships')
            ->where('user_id', $request->user()->id)
            ->where('department_id', $assessment->department_id)
            ->where(function ($query) use ($request, $assessment) {
                $query->where('role', 'admin');
                if ((int) $assessment->assessor_id === (int) $request->user()->id) {
                    $query->orWhere('role', 'assessor');
                }
            })
            ->exists();

        abort_unless(
            $allowed,
            403,
            'Only the department Admin or the assessment owner can edit this assessment.'
        );

        abort_if(
            $assessment->asset_deleted_at !== null,
            409,
            'This asset is archived.'
        );

        abort_unless(
            in_array($assessment->status, ['draft', 'submitted', 'returned', 'verified', 'archived'], true),
            409,
            'This assessment status cannot be edited.'
        );
    }

    public function edit(Request $request, string $assessment)
    {
        $record = $this->assessmentQuery($request, $assessment)->first();

        abort_if($record === null, 404);

        $this->ensureCanCorrect($request, $record);

        $record->inputs = json_decode(
            $record->inputs,
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $reviewComment = DB::table('risk_reviews')
            ->whereNull('risk_treatment_id')
            ->where('risk_assessment_id', $record->id)
            ->where('revision', $record->revision)
            ->where('decision', 'returned')
            ->orderByDesc('id')
            ->value('comment');

        return view('assessments.edit', [
            'assessment' => $record,
            'reviewComment' => $reviewComment,
        ]);
    }

    public function update(
        Request $request,
        string $assessment,
        RiskCalculator $calculator
    ) {
        $request->validate([
            'revision' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'string'],
        ]);

        DB::transaction(function () use ($request, $assessment, $calculator) {
            $record = $this->assessmentQuery($request, $assessment)
                ->lockForUpdate()
                ->first();

            abort_if($record === null, 404);

            $this->ensureCanCorrect($request, $record);

            abort_unless(
                (int) $record->revision === (int) $request->input('revision')
                    && $record->status === $request->input('status'),
                409,
                'The assessment has changed. Refresh the page.'
            );

            // Keep the existing asset and scoring profile.
            $profile = $record->profile;
            $max = $profile === 'nacsa-5-2024' ? 5 : 3;

            $scoreKeys = array_merge(
                ['c', 'i', 'a', 'likelihood'],
                $profile === 'usim-2026'
                    ? ['operations', 'financial', 'reputation']
                    : ['loss']
            );

            $rules = [
                'risk_asset_id' => ['prohibited'],
                'profile' => ['prohibited'],
                'assessed_on' => ['required', 'date_format:Y-m-d'],
                'threat' => ['required', 'string', 'max:5000'],
                'vulnerability' => ['required', 'string', 'max:5000'],
                'existing_controls' => ['nullable', 'string', 'max:5000'],
                'risk_owner' => ['required', 'string', 'max:180'],
            ];

            foreach ($scoreKeys as $key) {
                $rules[$key] = ['required', 'integer', "between:1,$max"];
            }

            $validated = $request->validate($rules);

            $inputs = ['profile' => $profile];

            foreach ($scoreKeys as $key) {
                $inputs[$key] = (int) $validated[$key];
            }

            $result = $calculator->calculate($inputs);
            $revision = (int) $record->revision + 1;
            $now = now();

            // Preserve the previous assessment contents in the audit event.
            $previousSnapshot = [
                'risk_asset_id' => $record->risk_asset_id,
                'assessor_id' => $record->assessor_id,
                'assessed_on' => $record->assessed_on,
                'profile' => $record->profile,
                'engine_version' => $record->engine_version,
                'threat' => $record->threat,
                'vulnerability' => $record->vulnerability,
                'existing_controls' => $record->existing_controls,
                'risk_owner' => $record->risk_owner,
                'status' => $record->status,
                'revision' => $record->revision,
                'risk_basis' => $record->risk_basis,
                'inputs' => json_decode(
                    $record->inputs,
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                ),
                'result' => json_decode(
                    $record->result,
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                ),
            ];

            DB::table('risk_assessments')
                ->where('id', $record->id)
                ->update([
                    // The person correcting becomes this revision's assessor.
                    'assessor_id' => $request->user()->id,
                    'assessed_on' => $validated['assessed_on'],
                    'threat' => $validated['threat'],
                    'vulnerability' => $validated['vulnerability'],
                    'existing_controls' => $validated['existing_controls'] ?? null,
                    'risk_owner' => $validated['risk_owner'],
                    'engine_version' => $result['version'],
                    'inputs' => json_encode($inputs, JSON_THROW_ON_ERROR),
                    'result' => json_encode($result, JSON_THROW_ON_ERROR),
                    'risk_basis' => 'inherent',
                    'status' => 'draft',
                    'revision' => $revision,
                    'updated_at' => $now,
                ]);

            DB::table('risk_audit_events')->insert([
                'actor_id' => $request->user()->id,
                'risk_assessment_id' => $record->id,
                'action' => 'assessment.edited',
                'metadata' => json_encode([
                    'department_id' => $record->department_id,
                    'previous_snapshot' => $previousSnapshot,
                    'new_revision' => $revision,
                    'risk_basis' => 'inherent',
                    'from_status' => $record->status,
                    'to_status' => 'draft',
                ], JSON_THROW_ON_ERROR),
                'created_at' => $now,
            ]);
        });

        return redirect()
            ->route('assessments.review', ['assessment' => $assessment])
            ->with(
                'success',
                'Changes saved as a new draft revision. Previous reviews and treatments are historical; submit this revision for a fresh review.'
            );
    }
}