<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\TreatmentRevision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class RiskTreatmentReviewController extends Controller
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

    private function canReview(
        Request $request,
        object $assessment,
        object $treatment
    ): bool {
        $userId = (int) $request->user()->id;

        $isReviewer = DB::table('department_memberships')
            ->where('user_id', $userId)
            ->where('department_id', $assessment->department_id)
            ->where('role', 'reviewer')
            ->exists();

        // Identify who recorded implementation from its audit event.
        $implementers = DB::table('risk_audit_events')
            ->where('risk_assessment_id', $assessment->id)
            ->where('action', 'treatment.implemented')
            ->where('metadata->treatment_id', (int) $treatment->id)
            ->pluck('actor_id');

        return $isReviewer
            && (int) $assessment->assessor_id !== $userId
            && $implementers->isNotEmpty()
            && !$implementers->contains($userId);
    }

    private function reviewQuery(object $assessment, object $treatment)
    {
        return DB::table('risk_reviews')
            ->where('risk_assessment_id', $assessment->id)
            ->where('risk_treatment_id', $treatment->id)
            ->where('revision', $treatment->assessment_revision)
            ->where('stage', 'residual');
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

        $review = $this->reviewQuery($record, $plan)
            ->leftJoin('users', 'risk_reviews.reviewer_id', '=', 'users.id')
            ->select('risk_reviews.*', 'users.name as reviewer_name')
            ->first();

        $canReview = $this->canReview($request, $record, $plan);

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

        return view('assessments.treatment-review', [
            'assessment' => $record,
            'treatment' => $plan,
            'isCurrentTreatment' => TreatmentRevision::isCurrent($record, $plan),
            'baseline' => TreatmentRevision::baseline($record, $plan),
            'review' => $review,
            'canReview' => $canReview,
        ]);
    }

    public function store(
        Request $request,
        string $assessment,
        string $treatment
    ) {
        DB::transaction(function () use ($request, $assessment, $treatment) {
            $record = $this->assessmentQuery($request, $assessment)
                ->lockForUpdate()
                ->first();

            abort_if($record === null, 404);

            $plan = DB::table('risk_treatments')
                ->where('id', $treatment)
                ->where('risk_assessment_id', $record->id)
                ->lockForUpdate()
                ->first();

            abort_if($plan === null, 404);
            abort_unless(TreatmentRevision::isCurrent($record, $plan), 409,
                'This treatment belongs to an earlier or unknown revision and is view only. Create a new plan after verifying the current assessment.');


            abort_unless(
                $this->canReview($request, $record, $plan),
                403,
                'An independent department Reviewer is required.'
            );

            abort_if(
                $record->asset_deleted_at !== null,
                409,
                'This asset is archived.'
            );

            abort_unless(
                $record->status === 'verified',
                409,
                'The assessment must be verified first.'
            );

            abort_if(
                $plan->implemented_at === null
                    || $plan->residual_inputs === null
                    || $plan->residual_result === null,
                409,
                'Record implementation and residual risk first.'
            );

            abort_if(
                $this->reviewQuery($record, $plan)->exists(),
                409,
                'A residual review has already been recorded.'
            );

            $validated = $request->validate([
                'revision' => ['required', 'integer', 'min:1'],
                'comment' => ['required', 'string', 'max:5000'],
            ]);

            abort_unless(
                (int) $record->revision === (int) $validated['revision'],
                409,
                'The assessment has changed. Refresh the page.'
            );

            $now = now();

            $reviewId = DB::table('risk_reviews')->insertGetId([
                'risk_assessment_id' => $record->id,
                'risk_treatment_id' => $plan->id,
                'reviewer_id' => $request->user()->id,
                'revision' => $record->revision,
                'stage' => 'residual',
                'decision' => 'verified',
                'comment' => $validated['comment'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('risk_audit_events')->insert([
                'actor_id' => $request->user()->id,
                'risk_assessment_id' => $record->id,
                'action' => 'treatment.residual_verified',
                'metadata' => json_encode([
                    'review_id' => $reviewId,
                    'treatment_id' => $plan->id,
                    'department_id' => $record->department_id,
                    'assessment_revision' => $record->revision,
                    'implementation_evidence' => $plan->implementation_evidence,
                    'implemented_at' => $plan->implemented_at,
                    'residual_inputs' => json_decode(
                        $plan->residual_inputs,
                        true,
                        512,
                        JSON_THROW_ON_ERROR
                    ),
                    'residual_result' => json_decode(
                        $plan->residual_result,
                        true,
                        512,
                        JSON_THROW_ON_ERROR
                    ),
                ], JSON_THROW_ON_ERROR),
                'created_at' => $now,
            ]);
        });

        return redirect()
            ->route('assessments.treatment-review', [
                'assessment' => $assessment,
                'treatment' => $treatment,
            ])
            ->with('success', 'Implementation and residual risk verified.');
    }
}