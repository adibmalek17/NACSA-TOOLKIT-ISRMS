@extends('layouts.toolkit')

@section('title')
Assessment #{{ $assessment->id }} | NACSA Toolkit
@endsection
@section('page-label', 'Assessments')
@section('page-class', ' assessment-workspace')

@section('content')
@php
    $user = auth()->user();
@endphp
        
        <div class="page-heading"><div>
            <p class="eyebrow">Assessment overview</p>
            <h1>Assessment #{{ $assessment->id }}</h1>
            <p>{{ $assessment->asset_code }}<br>{{ $assessment->asset_name }}</p>
        </div></div>
@include('partials.assessment-navigation', ['activeTab' => 'overview'])
@include('partials.risk-comparison')


    @if ($assessment->asset_deleted_at)
        <p class="overview-notice">
            This asset is archived. Its saved assessment remains available
            for reference.
        </p>
    @endif

    <section class="overview-panel">
        <h2>Assessment details</h2>

        <dl class="grid">
            <div>
                <dt>Department</dt>
                <dd>{{ $assessment->department_name }}</dd>
            </div>

            <div>
                <dt>Assessment date</dt>
                <dd>{{ $assessment->assessed_on }}</dd>
            </div>

            <div>
                <dt>Assessor</dt>
                <dd>{{ $assessment->assessor_name ?? 'Unavailable' }}</dd>
            </div>

            <div>
                <dt>Risk owner</dt>
                <dd>{{ $assessment->risk_owner }}</dd>
            </div>

            <div>
                <dt>Status</dt>
                <dd>{{ ucfirst($assessment->status) }}</dd>
            </div>

            <div>
                <dt>Revision</dt>
                <dd>{{ $assessment->revision }}</dd>
            </div>

            <div>
                <dt>Scoring profile</dt>
                <dd>{{ $assessment->profile }}</dd>
            </div>

            <div>
                <dt>Engine version</dt>
                <dd>{{ $assessment->engine_version }}</dd>
            </div>
        </dl>
    </section>

    <section class="overview-panel">
        <h2>Risk description</h2>

        <h3>Threat</h3>
        <p class="text">{{ $assessment->threat }}</p>

        <h3>Vulnerability</h3>
        <p class="text">{{ $assessment->vulnerability }}</p>

        <h3>Existing controls</h3>
        <p class="text">{{ $assessment->existing_controls ?: 'No controls recorded.' }}</p>
    </section>

    <div class="overview-columns">
    <section class="overview-panel">
        <h2>Inherent Risk Inputs</h2>

        <table>
            <thead>
                <tr>
                    <th scope="col">Input</th>
                    <th scope="col">Value</th>
                </tr>
            </thead>

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
                    @if (array_key_exists($key, $assessment->inputs))
                        <tr>
                            <th scope="row">{{ $label }}</th>
                            <td>{{ $assessment->inputs[$key] }}</td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </section>

    <section class="overview-panel">
        <h2>Inherent Risk Result</h2>

        <div class="result risk-{{ \App\Services\RiskPresentation::tone($assessment->result, $assessment->profile) }}">
            <p>Risk score</p>
            <p class="score">{{ $assessment->result['score'] ?? 'Not available' }}</p>
            <p>
                Risk level:
                @include('partials.risk-badge', ['riskResult' => $assessment->result, 'riskProfile' => $assessment->profile])
            </p>
        </div>

        <dl class="grid">
            <div>
                <dt>CIA total</dt>
                <dd>{{ $assessment->result['ciaTotal'] ?? 'Not available' }}</dd>
            </div>

            <div>
                <dt>Asset value</dt>
                <dd>{{ $assessment->result['asset'] ?? 'Not available' }}</dd>
            </div>

            <div>
                <dt>Impact value</dt>
                <dd>{{ $assessment->result['impact'] ?? 'Not available' }}</dd>
            </div>
        </dl>
    </section>
</div>
        
    
@endsection
