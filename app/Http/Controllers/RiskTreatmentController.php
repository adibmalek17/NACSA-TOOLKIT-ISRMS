<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

final class RiskTreatmentController extends Controller
{
    private const OPTIONS = [
        'accept' => 'Accept',
        'reduce' => 'Reduce',
        'transfer' => 'Transfer',
        'avoid' => 'Avoid',
    ];

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

    public function index(Request $request, string $assessment)
    {
        $record = $this->assessmentQuery($request, $assessment)->first();

        abort_if($record === null, 404);

        $treatments = DB::table('risk_treatments')
            ->where('risk_assessment_id', $record->id)
            ->orderByDesc('id')
            ->get();

        return view('assessments.treatments', [
            'assessment' => $record,
            'treatments' => $treatments,
            'options' => self::OPTIONS,
            'canManage' => $this->canManage($request, $record),
        ]);
    }

    public function store(Request $request, string $assessment)
    {
        DB::transaction(function () use ($request, $assessment) {
            $record = $this->assessmentQuery($request, $assessment)
                ->lockForUpdate()
                ->first();

            abort_if($record === null, 404);

            abort_unless(
                $this->canManage($request, $record),
                403,
                'You cannot add treatment plans in this department.'
            );

            abort_if(
                $record->asset_deleted_at !== null,
                409,
                'This asset is archived.'
            );

            abort_unless(
                $record->status === 'verified',
                409,
                'Treatment plans require a verified assessment.'
            );

            $rules = [
                'revision' => ['required', 'integer', 'min:1'],
                'option' => ['required', Rule::in(array_keys(self::OPTIONS))],
                'rationale' => ['required', 'string', 'max:5000'],
                'planned_controls' => ['nullable', 'string', 'max:5000'],
                'responsible_person' => ['required', 'string', 'max:255'],
                'starts_on' => ['nullable', 'date_format:Y-m-d'],
                'due_on' => ['nullable', 'date_format:Y-m-d'],
            ];

            if ($request->filled('starts_on')) {
                $rules['due_on'][] = 'after_or_equal:starts_on';
            }

            $validated = $request->validate($rules);

            abort_unless(
                (int) $record->revision === (int) $validated['revision'],
                409,
                'The assessment has changed. Refresh the page.'
            );

            $now = now();

            $treatmentId = DB::table('risk_treatments')->insertGetId([
                'risk_assessment_id' => $record->id,
                'assessment_revision' => (int) $record->revision,
                'option' => $validated['option'],
                'rationale' => $validated['rationale'],
                'planned_controls' => $validated['planned_controls'] ?? null,
                'responsible_person' => $validated['responsible_person'],
                'starts_on' => $validated['starts_on'] ?? null,
                'due_on' => $validated['due_on'] ?? null,
                'annex_a_references' => null,
                'implementation_evidence' => null,
                'residual_inputs' => null,
                'residual_result' => null,
                'implemented_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('risk_audit_events')->insert([
                'actor_id' => $request->user()->id,
                'risk_assessment_id' => $record->id,
                'action' => 'treatment.created',
                'metadata' => json_encode([
                    'treatment_id' => $treatmentId,
                    'department_id' => $record->department_id,
                    'assessment_revision' => $record->revision,
                    'option' => $validated['option'],
                ], JSON_THROW_ON_ERROR),
                'created_at' => $now,
            ]);
        });

        return redirect()
            ->route('assessments.treatments', ['assessment' => $assessment])
            ->with('success', 'Treatment plan saved successfully.');
    }
}