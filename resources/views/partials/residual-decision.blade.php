<section class="decision-panel">
        <h2>Review Decision</h2>

        @if ($review)
            <p>
                Decision: <strong>{{ ucfirst($review->decision) }}</strong><br>
                Reviewer: {{ $review->reviewer_name ?? 'Unavailable' }}<br>
                Assessment revision: {{ $review->revision }}<br>
                Recorded at: {{ $review->created_at }}
            </p>

            <p class="text">{{ $review->comment }}</p>
        @elseif (!$isCurrentTreatment)
            <p>This historical plan cannot receive a review for the current revision.</p>
        @elseif ($assessment->asset_deleted_at !== null)
            <p>This asset is archived. Reviews are view only.</p>
        @elseif ($assessment->status !== 'verified')
            <p>The assessment must be verified first.</p>
        @elseif (
            $treatment->implemented_at === null
            || $treatment->residual_inputs === null
            || $treatment->residual_result === null
        )
            <p>Implementation and residual risk must be recorded first.</p>
        @elseif (!$canReview)
            <p>
                An independent department Reviewer must verify this result.
                The assessor and the person who recorded implementation
                cannot verify it themselves.
            </p>
        @else
            <p>
                Check the implementation evidence and scoring inputs before
                verifying. If corrections are needed, leave this record
                unverified; this page currently supports verification only.
            </p>

            <form method="POST"
                  action="{{ route('assessments.treatment-review.store', [
                      'assessment' => $assessment->id,
                      'treatment' => $treatment->id,
                  ]) }}">
                @csrf
                <input type="hidden" name="treatment_id" value="{{ $treatment->id }}">

                <input type="hidden"
                       name="revision"
                       value="{{ $assessment->revision }}">

                <label>
                    Review comment
                    <textarea name="comment"
                              maxlength="5000"
                              required>{{ (string) old('treatment_id') === (string) $treatment->id ? old('comment') : '' }}</textarea>
                </label>

                <button type="submit">
                    Verify implementation and residual risk
                </button>
            </form>
        @endif
    </section>
