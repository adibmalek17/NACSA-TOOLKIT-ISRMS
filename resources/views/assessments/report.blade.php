@extends('layouts.toolkit')

@section('title')
Assessment {{ $assessment->id }} Report | NACSA Toolkit
@endsection
@section('page-label', 'Assessments')
@section('page-class', 'report-page')

@section('content')

<div class="page-heading"><div><h1>NACSA Toolkit — Assessment Report</h1></div></div>
@include('partials.assessment-navigation', ['activeTab' => 'report'])


    <div class="toolbar">
        

        <button type="button" id="export-excel" data-assessment-id="{{ $assessment->id }}">Export Excel</button>
    </div>

    

    <p>
    Generated: {{ now()->timezone('Asia/Kuala_Lumpur')->format('d/m/Y h:i:s A') }}
    (Malaysia time)
</p>

    <section>
        <h2>Assessment details</h2>

        <p>
            <strong>Asset:</strong>
            {{ $assessment->asset_code }} — {{ $assessment->asset_name }}<br>

            <strong>Department:</strong>
            {{ $assessment->department_name }}<br>

            <strong>Assessment date:</strong>
            {{ $assessment->assessed_on }}<br>

            <strong>Assessor:</strong>
            {{ $assessment->assessor_name ?? 'Unavailable' }}<br>

            <strong>Risk owner:</strong>
            {{ $assessment->risk_owner }}<br>

            <strong>Status:</strong>
            {{ ucfirst($assessment->status) }}<br>

            <strong>Scoring profile:</strong>
            {{ $assessment->profile }}<br>

            <strong>Engine version:</strong>
            {{ $assessment->engine_version }}
        </p>

        @if ($assessment->asset_deleted_at !== null)
            <p><strong>This asset is archived.</strong></p>
        @endif

        <h3>Threat</h3>
        <p class="text">{{ $assessment->threat }}</p>

        <h3>Vulnerability</h3>
        <p class="text">{{ $assessment->vulnerability }}</p>

        <h3>Existing controls</h3>
        <p class="text">{{ $assessment->existing_controls ?? 'Not provided.' }}</p>
    </section>

    @php
        $labels = [
            'c' => 'Confidentiality',
            'i' => 'Integrity',
            'a' => 'Availability',
            'likelihood' => 'Likelihood',
            'loss' => 'Loss',
            'operations' => 'Operations impact',
            'financial' => 'Financial impact',
            'reputation' => 'Reputation impact',
        ];
    @endphp

    <section>
        <h2>Saved assessment scoring</h2>

        <p>
            <strong>Score:</strong> {{ $assessment->result['score'] }}<br>
            <strong>Risk label:</strong> {{ $assessment->result['label'] }}<br>
            <strong>Override applied:</strong>
            {{ $assessment->result['override'] ? 'Yes' : 'No' }}
        </p>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Input</th><th>Value</th></tr>
                </thead>
                <tbody>
                    @foreach ($labels as $key => $label)
                        @if (isset($assessment->inputs[$key]))
                            <tr>
                                <th>{{ $label }}</th>
                                <td>{{ $assessment->inputs[$key] }}</td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section>
        <h2>Assessment review history</h2>

        @forelse ($assessmentReviews as $review)
            <article>
                <h3>
                    Revision {{ $review->revision }}
                    — {{ ucfirst($review->decision) }}
                </h3>

                <p>
                    Reviewer: {{ $review->reviewer_name ?? 'Unavailable' }}<br>
                    Recorded at: {{ $review->created_at }}
                </p>

                <p class="text">{{ $review->comment }}</p>
            </article>
        @empty
            <p>No assessment reviews recorded.</p>
        @endforelse
    </section>

    <section>
        <h2>Current-revision treatment plans and residual risk</h2>
        <p>Earlier plans remain on the treatment plans page as historical records.</p>

        @forelse ($treatments as $treatment)
            <article>
                <h3>
                    Plan #{{ $treatment->id }}
                    — {{ ucfirst($treatment->option) }}
                </h3>

                <p>
                    <strong>Responsible person:</strong>
                    {{ $treatment->responsible_person }}<br>

                    <strong>Start date:</strong>
                    {{ $treatment->starts_on ?? 'Not set' }}<br>

                    <strong>Due date:</strong>
                    {{ $treatment->due_on ?? 'Not set' }}<br>

                    <strong>Implementation recorded at:</strong>
                    {{ $treatment->implemented_at ?? 'Not recorded' }}
                </p>

                <h4>Rationale</h4>
                <p class="text">{{ $treatment->rationale }}</p>

                <h4>Planned controls or actions</h4>
                <p class="text">{{ $treatment->planned_controls ?? 'Not provided.' }}</p>

                <h4>Implementation evidence</h4>
                <p class="text">{{ $treatment->implementation_evidence ?? 'Not recorded.' }}</p>

                @if ($treatment->residual_result !== null)
                    <h4>Residual risk</h4>

                    <p>
                        <strong>Score:</strong>
                        {{ $treatment->residual_result['score'] }}<br>

                        <strong>Risk label:</strong>
                        {{ $treatment->residual_result['label'] }}<br>

                        <strong>Scoring profile:</strong>
                        {{ $treatment->residual_result['profile'] }}<br>

                        <strong>Engine version:</strong>
                        {{ $treatment->residual_result['version'] }}
                    </p>

                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr><th>Residual input</th><th>Value</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($labels as $key => $label)
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
                @else
                    <p>Residual risk has not been recorded.</p>
                @endif

                @php
                    $residualReview = $residualReviews->get($treatment->id);
                @endphp

                <h4>Residual review for assessment revision {{ $assessment->revision }}</h4>

                @if ($residualReview)
                    <p>
                        <strong>Decision:</strong>
                        {{ ucfirst($residualReview->decision) }}<br>

                        <strong>Reviewer:</strong>
                        {{ $residualReview->reviewer_name ?? 'Unavailable' }}<br>

                        <strong>Recorded at:</strong>
                        {{ $residualReview->created_at }}
                    </p>

                    <p class="text">{{ $residualReview->comment }}</p>
                @else
                    <p>No residual review recorded for this revision.</p>
                @endif
            </article>
        @empty
            <p>No treatment plans recorded.</p>
        @endforelse
    </section>

@endsection

@section('scripts')
@include('partials.report-excel-script')
@endsection
