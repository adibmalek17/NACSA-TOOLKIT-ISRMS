<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class RiskComparison
{
    public static function basisLabel(object $assessment): string
    {
        return ($assessment->risk_basis ?? null) === 'inherent'
            ? 'Inherent risk' : 'Initial risk — basis not recorded';
    }

    /** Call only with assessments already scoped to the user's department access. */
    public static function forAssessments(Collection $assessments): Collection
    {
        if ($assessments->isEmpty()) {
            return collect();
        }

        $plans = DB::table('risk_treatments')
            ->whereIn('risk_assessment_id', $assessments->pluck('id'))
            ->orderBy('id')->get()->groupBy('risk_assessment_id');
        $reviews = DB::table('risk_reviews')
            ->whereIn('risk_assessment_id', $assessments->pluck('id'))
            ->where('stage', 'residual')->where('decision', 'verified')
            ->whereNotNull('risk_treatment_id')->get();

        return $assessments->mapWithKeys(function ($assessment) use ($plans, $reviews) {
            $initial = self::decode($assessment->result);
            $rows = $plans->get($assessment->id, collect())
                ->filter(fn ($plan) => TreatmentRevision::isCurrent($assessment, $plan))
                ->map(function ($plan) use ($assessment, $reviews, $initial) {
                    $result = self::decode($plan->residual_result);
                    $recorded = $plan->implemented_at !== null
                        && $plan->residual_inputs !== null && isset($result['score']);
                    $verified = $recorded && $reviews->contains(fn ($review) =>
                        (int) $review->risk_assessment_id === (int) $assessment->id
                        && (int) $review->risk_treatment_id === (int) $plan->id
                        && (int) $review->revision === (int) $assessment->revision);
                    $comparable = $recorded && ($assessment->risk_basis ?? null) === 'inherent'
                        && ($result['profile'] ?? null) === $assessment->profile
                        && ($result['version'] ?? null) === $assessment->engine_version
                        && isset($initial['score']);

                    return [
                        'plan_id' => $plan->id,
                        'result' => $recorded ? $result : null,
                        'status' => !$recorded ? 'Not assessed' : ($verified ? 'Verified' : 'Awaiting verification'),
                        // Positive means a reduction. Zero/negative changes are valid too.
                        'reduction' => $comparable ? $initial['score'] - $result['score'] : null,
                    ];
                })->values()->all();

            return [$assessment->id => $rows];
        });
    }

    private static function decode(mixed $value): array
    {
        return is_array($value) ? $value : ($value === null ? [] : json_decode($value, true, 512, JSON_THROW_ON_ERROR));
    }
}
