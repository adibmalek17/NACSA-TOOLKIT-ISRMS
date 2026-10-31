@extends('layouts.toolkit')

@section('title')
Assessment {{ $assessment->id }} Report | NACSA Toolkit
@endsection
@section('page-label', 'Assessments')
@section('page-class', 'report-page assessment-workspace')

@section('content')

<div class="page-heading"><div><p class="eyebrow">Assessment {{ $assessment->id }}</p><h1>Assessment Report</h1></div><button type="button" id="export-excel" data-assessment-id="{{ $assessment->id }}">Export Excel</button></div>
@include('partials.assessment-navigation', ['activeTab' => 'report'])
@include('partials.risk-comparison')
@include('partials.report-risk-inputs')


<p class="hint">Generated {{ now()->timezone('Asia/Kuala_Lumpur')->format('d/m/Y h:i A') }} (Malaysia time)</p>

<div class="report-detail-grid">
    <section aria-labelledby="report-assessment-heading">
        <h2 id="report-assessment-heading">Assessment Details</h2>
        <div class="table-wrap"><table class="report-details-table">
            <thead><tr><th scope="col">Field</th><th scope="col">Details</th></tr></thead>
            <tbody>
                @foreach ([
                    'Asset Code' => $assessment->asset_code,
                    'Asset Name' => $assessment->asset_name,
                    'Department' => $assessment->department_name,
                    'Assessment Date' => $assessment->assessed_on,
                    'Assessor' => $assessment->assessor_name ?? 'Unavailable',
                    'Risk Owner' => $assessment->risk_owner,
                    'Status' => ucfirst($assessment->status),
                    'Revision' => $assessment->revision,
                    'Scoring Profile' => $assessment->profile,
                    'Engine Version' => $assessment->engine_version,
                    'Threat' => $assessment->threat,
                    'Vulnerability' => $assessment->vulnerability,
                    'Existing Controls' => $assessment->existing_controls ?: 'Not recorded',
                ] as $field => $value)
                    <tr><th scope="row">{{ $field }}</th><td class="report-value">{{ $value }}</td></tr>
                @endforeach
            </tbody>
        </table></div>
        @if ($assessment->asset_deleted_at !== null)
            <p class="notice">This asset is archived.</p>
        @endif
        @php
            $assessmentDecision = $assessmentReviews->where('revision', $assessment->revision)->last();
        @endphp
        @if ($assessmentDecision)
            <h3>Assessment Decision</h3>
            <div class="table-wrap"><table class="report-details-table">
                <thead><tr><th scope="col">Field</th><th scope="col">Details</th></tr></thead>
                <tbody>
                    <tr><th scope="row">Decision</th><td>{{ ucfirst($assessmentDecision->decision) }}</td></tr>
                    <tr><th scope="row">Reviewer</th><td>{{ $assessmentDecision->reviewer_name ?? 'Unavailable' }}</td></tr>
                    <tr><th scope="row">Recorded At</th><td>{{ $assessmentDecision->created_at }}</td></tr>
                    <tr><th scope="row">Comment</th><td class="report-value">{{ $assessmentDecision->comment }}</td></tr>
                </tbody>
            </table></div>
        @endif
    </section>

    <section aria-labelledby="report-treatment-heading">
        <h2 id="report-treatment-heading">Treatment Implementation</h2>
        @forelse ($treatments as $treatment)
            @php
                $residualReview = $residualReviews->get($treatment->id);
            @endphp
            <h3>Treatment Plan {{ $treatment->id }}</h3>
            <div class="table-wrap"><table class="report-details-table">
                <thead><tr><th scope="col">Field</th><th scope="col">Details</th></tr></thead>
                <tbody>
                    @foreach ([
                        'Treatment Option' => ucfirst($treatment->option),
                        'Responsible Person' => $treatment->responsible_person,
                        'Start Date' => $treatment->starts_on ?? 'Not set',
                        'Due Date' => $treatment->due_on ?? 'Not set',
                        'Implemented At' => $treatment->implemented_at ?? 'Not recorded',
                        'Rationale' => $treatment->rationale,
                        'Planned Controls' => $treatment->planned_controls ?: 'Not recorded',
                        'Implementation Evidence' => $treatment->implementation_evidence ?: 'Not recorded',
                        'Residual Score' => $treatment->residual_result['score'] ?? 'Not assessed',
                    ] as $field => $value)
                        <tr><th scope="row">{{ $field }}</th><td class="report-value">{{ $value }}</td></tr>
                    @endforeach
                    <tr><th scope="row">Residual Risk Level</th><td>@include('partials.risk-badge', ['riskResult' => $treatment->residual_result ?? [], 'riskProfile' => $assessment->profile])</td></tr>
                    <tr><th scope="row">Review Decision</th><td>{{ $residualReview ? ucfirst($residualReview->decision) : 'Not recorded' }}</td></tr>
                    @if ($residualReview)
                        <tr><th scope="row">Reviewer</th><td>{{ $residualReview->reviewer_name ?? 'Unavailable' }}</td></tr>
                        <tr><th scope="row">Reviewed At</th><td>{{ $residualReview->created_at }}</td></tr>
                        <tr><th scope="row">Review Comment</th><td class="report-value">{{ $residualReview->comment }}</td></tr>
                    @endif
                </tbody>
            </table></div>
        @empty
            <p class="assessment-empty">No treatment plans recorded for this revision.</p>
        @endforelse
    </section>
</div>

@endsection

@section('scripts')
@include('partials.report-excel-script')
@endsection
