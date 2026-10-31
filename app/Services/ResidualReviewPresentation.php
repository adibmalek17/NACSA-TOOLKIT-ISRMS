<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;

final class ResidualReviewPresentation
{
    /** The caller must first authorize access to the assessment. */
    public static function currentPlans(object $assessment, int $userId): array
    {
        $plans = DB::table('risk_treatments')
            ->where('risk_assessment_id', $assessment->id)
            ->where('assessment_revision', $assessment->revision)
            ->orderBy('id')->get();
        $reviews = DB::table('risk_reviews')
            ->leftJoin('users', 'risk_reviews.reviewer_id', '=', 'users.id')
            ->where('risk_reviews.risk_assessment_id', $assessment->id)
            ->where('risk_reviews.revision', $assessment->revision)
            ->where('risk_reviews.stage', 'residual')
            ->select('risk_reviews.*', 'users.name as reviewer_name')
            ->get()->keyBy('risk_treatment_id');
        $isReviewer = DB::table('department_memberships')
            ->where('user_id', $userId)->where('department_id', $assessment->department_id)
            ->where('role', 'reviewer')->exists();
        $events = DB::table('risk_audit_events')
            ->where('risk_assessment_id', $assessment->id)
            ->where('action', 'treatment.implemented')->get(['actor_id', 'metadata']);

        return $plans->map(function ($plan) use ($assessment, $userId, $isReviewer, $reviews, $events) {
            $implementers = $events->filter(function ($event) use ($plan) {
                $metadata = json_decode($event->metadata, true, 512, JSON_THROW_ON_ERROR);
                return (int) ($metadata['treatment_id'] ?? 0) === (int) $plan->id;
            })->pluck('actor_id');
            foreach (['residual_inputs', 'residual_result'] as $field) {
                $plan->$field = $plan->$field === null ? null : json_decode($plan->$field, true, 512, JSON_THROW_ON_ERROR);
            }
            return [
                'treatment' => $plan,
                'review' => $reviews->get($plan->id),
                'isCurrentTreatment' => true,
                'canReview' => $isReviewer && (int) $assessment->assessor_id !== $userId
                    && $implementers->isNotEmpty() && !$implementers->contains($userId),
            ];
        })->all();
    }
}
