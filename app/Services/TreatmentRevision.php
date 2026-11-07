<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;

final class TreatmentRevision
{
    public static function isCurrent(object $assessment, object $treatment): bool
    {
        return $treatment->assessment_revision !== null
            && (int) $treatment->assessment_revision === (int) $assessment->revision;
    }

    public static function baseline(object $assessment, object $treatment): array
    {
        if (self::isCurrent($assessment, $treatment)) {
            return [
                'risk_basis' => $assessment->risk_basis ?? null,
                'expected_inputs' => ExpectedRisk::decode($assessment->expected_inputs ?? null),
                'expected_result' => ExpectedRisk::decode($assessment->expected_result ?? null),
                'inputs' => is_array($assessment->inputs) ? $assessment->inputs : json_decode($assessment->inputs, true, 512, JSON_THROW_ON_ERROR),
                'result' => is_array($assessment->result) ? $assessment->result : json_decode($assessment->result, true, 512, JSON_THROW_ON_ERROR),
            ];
        }
        if ($treatment->assessment_revision !== null) {
            $events = DB::table('risk_audit_events')->where('risk_assessment_id', $assessment->id)
                ->whereIn('action', ['assessment.edited', 'assessment.corrected'])->orderBy('id')->get();
            foreach ($events as $event) {
                $metadata = json_decode($event->metadata, true, 512, JSON_THROW_ON_ERROR);
                $snapshot = $metadata['previous_snapshot'] ?? [];
                if ((int) ($snapshot['revision'] ?? 0) === (int) $treatment->assessment_revision) {
                    return ['expected_inputs' => $snapshot['expected_inputs'] ?? [], 'expected_result' => $snapshot['expected_result'] ?? [], 'risk_basis' => $snapshot['risk_basis'] ?? null, 'inputs' => $snapshot['inputs'] ?? [], 'result' => $snapshot['result'] ?? []];
                }
            }
        }
        return ['expected_inputs' => [], 'expected_result' => [], 'risk_basis' => null, 'inputs' => [], 'result' => []];
    }
}
