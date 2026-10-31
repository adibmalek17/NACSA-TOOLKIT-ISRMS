@extends('layouts.toolkit')

@section('title')
Residual Risk Review | NACSA Toolkit
@endsection
@section('page-label', 'Assessments')
@section('page-class', '')

@section('content')

<div class="page-heading"><div><h1>Residual risk review — Plan #{{ $treatment->id }}</h1></div></div>
@include('partials.assessment-navigation', ['activeTab' => 'treatments'])


    @if (!$isCurrentTreatment)
        <section><p>This plan belongs to assessment revision {{ $treatment->assessment_revision ?? 'unknown' }}.
            It is historical and view only. Its results do not approve the current revision.</p></section>
    @endif

    

    

    <p>
        Assessment: #{{ $assessment->id }}<br>
        Revision: {{ $assessment->revision }}<br>
        Asset: <strong>{{ $assessment->asset_code }}</strong><br>
        Profile: {{ $assessment->profile }}
    </p>

    @if (session('success'))
        <div class="success" role="status">{{ session('success') }}</div>
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
        <h2>Implementation evidence</h2>
        <p class="text">{{ $treatment->implementation_evidence ?? 'Not recorded.' }}</p>

        @if ($treatment->residual_result !== null)
            <h2>Risk comparison</h2>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Result</th>
                            <th>Verified assessment</th>
                            <th>Residual risk</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th>Score</th>
                            <td>{{ $baseline['result']['score'] ?? 'Unavailable' }}</td>
                            <td>{{ $treatment->residual_result['score'] }}</td>
                        </tr>
                        <tr>
                            <th>Risk label</th>
                            <td>{{ $baseline['result']['label'] ?? 'Unavailable' }}</td>
                            <td>{{ $treatment->residual_result['label'] }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <h3>Saved residual inputs</h3>

            <div class="table-wrap">
                <table>
                    <tbody>
                        @foreach ([
                            'c' => 'Confidentiality',
                            'i' => 'Integrity',
                            'a' => 'Availability',
                            'likelihood' => 'Likelihood',
                            'loss' => 'Loss',
                            'operations' => 'Operations impact',
                            'financial' => 'Financial impact',
                            'reputation' => 'Reputation impact',
                        ] as $key => $label)
                            @if (isset($treatment->residual_inputs[$key]))
                                <tr>
                                    <th>{{ $label }}</th>
                                    <td>{{ $treatment->residual_inputs[$key] }}</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <section>
        <h2>Review decision</h2>

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

                <input type="hidden"
                       name="revision"
                       value="{{ $assessment->revision }}">

                <label>
                    Review comment
                    <textarea name="comment"
                              maxlength="5000"
                              required>{{ old('comment') }}</textarea>
                </label>

                <button type="submit">
                    Verify implementation and residual risk
                </button>
            </form>
        @endif
    </section>

@endsection
