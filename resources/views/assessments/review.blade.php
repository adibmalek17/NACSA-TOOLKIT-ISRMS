@extends('layouts.toolkit')

@section('title')
Review Assessment #{{ $assessment->id }} | NACSA Toolkit
@endsection
@section('page-label', 'Assessments')
@section('page-class', '')

@section('content')

<div class="page-heading"><div><h1>Review assessment #{{ $assessment->id }}</h1></div></div>
@include('partials.assessment-navigation', ['activeTab' => 'review'])


    

    

    

    <p>
        Asset: <strong>{{ $assessment->asset_code }}</strong><br>
        Status: <strong>{{ ucfirst($assessment->status) }}</strong><br>
        Revision: {{ $assessment->revision }}
    </p>

    @if (session('success'))
        <div class="success" role="status">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="errors" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section>
        <h2>Workflow</h2>

        @if ($assessment->asset_deleted_at)
            <p>
                This asset is archived. Submission and review are unavailable.
                Existing review history remains visible.
            </p>
        @elseif ($assessment->status === 'draft')
            @if ($canSubmit)
                <p>
                    Check the assessment details before submitting it
                    to a Reviewer.
                </p>

                <form
                    method="POST"
                    action="{{ route('assessments.submit', ['assessment' => $assessment->id]) }}"
                >
                    @csrf
                    <input
                        type="hidden"
                        name="revision"
                        value="{{ $assessment->revision }}"
                    >

                    <button type="submit">Submit for review</button>
                </form>
            @else
                <p>
                    This assessment is still a draft.
                    An Admin or Assessor must submit it before review.
                </p>
            @endif
        @elseif ($assessment->status === 'submitted')
            @if ($canReview)
                <p>
                    Read the assessment details, then record your decision
                    and explanation.
                </p>

                <form
                    method="POST"
                    action="{{ route('assessments.review.store', ['assessment' => $assessment->id]) }}"
                >
                    @csrf
                    <input
                        type="hidden"
                        name="revision"
                        value="{{ $assessment->revision }}"
                    >

                    <label>
                        Decision
                        <select name="decision" required>
                            <option value="">Select a decision</option>
                            <option
                                value="verified"
                                @selected(old('decision') === 'verified')
                            >
                                Verify assessment
                            </option>
                            <option
                                value="returned"
                                @selected(old('decision') === 'returned')
                            >
                                Return for correction
                            </option>
                        </select>
                    </label>

                    <p>
                        <label>
                            Review comment
                            <textarea
                                name="comment"
                                maxlength="5000"
                                required
                            >{{ old('comment') }}</textarea>
                        </label>
                    </p>

                    <button type="submit">Save review decision</button>
                </form>
            @else
                <p>
                    Awaiting review by an eligible Reviewer in this department.
                    The assessor cannot review their own assessment.
                </p>
            @endif
        @elseif ($assessment->status === 'verified')
            <p>This assessment has been verified. See the review history below.</p>
        @elseif ($assessment->status === 'returned')
            <p>
                This assessment was returned for correction.
                Read the Reviewer’s comment below.
            </p>
        @else
            <p>No workflow action is available for the current status.</p>
        @endif
    </section>

    <section>
        <h2>Review history</h2>

        @forelse ($reviews as $review)
            <article>
                <h3>{{ ucfirst($review->decision) }}</h3>
                <p>
                    Reviewer: {{ $review->reviewer_name ?? 'Unavailable' }}<br>
                    Revision: {{ $review->revision }}<br>
                    Stage: {{ ucfirst($review->stage) }}<br>
                    Recorded at: {{ $review->created_at }}
                </p>
                <p class="comment">{{ $review->comment }}</p>
            </article>
        @empty
            <p>No review decisions have been recorded yet.</p>
        @endforelse
    </section>

@endsection
