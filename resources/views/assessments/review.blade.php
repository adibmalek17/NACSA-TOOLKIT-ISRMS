@extends('layouts.toolkit')

@section('title')
Review Assessment #{{ $assessment->id }} | NACSA Toolkit
@endsection
@section('page-label', 'Assessments')
@section('page-class', ' assessment-workspace')

@section('content')

<div class="page-heading"><div><h1>Review assessment #{{ $assessment->id }}</h1></div></div>
@include('partials.assessment-navigation', ['activeTab' => 'review'])


    

    

    

    @include('partials.assessment-context')

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
        <h2>Inherent Risk Review</h2>

        @if ($assessment->asset_deleted_at)
            <p>
                This asset is archived. Submission and review are unavailable.
                Existing review decisions are retained.
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
            <p>This assessment has been verified.</p>
        @elseif ($assessment->status === 'returned')
            <p>
                This assessment was returned for correction.
                Read the Reviewer’s comment below.
            </p>
        @else
            <p>No workflow action is available for the current status.</p>
        @endif
        @php
            $currentDecision = $reviews->firstWhere('revision', $assessment->revision);
        @endphp
        @if ($currentDecision && in_array($assessment->status, ['verified', 'returned'], true))
            <div class="current-review-decision">
                <p><strong>{{ ucfirst($currentDecision->decision) }}</strong><br>
                {{ $currentDecision->reviewer_name ?? 'Unavailable' }}<br>{{ $currentDecision->created_at }}</p>
                <p class="text">{{ $currentDecision->comment }}</p>
            </div>
        @endif
    </section>

    <section class="residual-review-list" aria-labelledby="residual-review-heading">
        <div class="panel-heading"><div><p class="eyebrow">After implementation</p><h2 id="residual-review-heading">Residual Risk Review</h2></div></div>
        @forelse ($residualPanels as $panel)
            <article class="treatment-card">
                <div class="panel-heading">
                    <div><h3>Treatment Plan {{ $panel['treatment']->id }}</h3><p>{{ $panel['treatment']->responsible_person }}</p></div>
                    @include('partials.risk-badge', ['riskResult' => $panel['treatment']->residual_result ?? [], 'riskProfile' => $assessment->profile])
                </div>
                <div class="assessment-context">
                    <div><span>Residual Score</span><strong>{{ $panel['treatment']->residual_result['score'] ?? 'Not assessed' }}</strong></div>
                    <div><span>Implementation Date</span><strong>{{ $panel['treatment']->implemented_at ?? 'Not recorded' }}</strong></div>
                </div>
                <h4>Implementation Evidence</h4>
                <p class="text">{{ $panel['treatment']->implementation_evidence ?? 'Not recorded.' }}</p>
                @include('partials.residual-decision', $panel)
            </article>
        @empty
            <div class="assessment-empty">No treatment plans are available for this revision.</div>
        @endforelse
    </section>

@endsection
