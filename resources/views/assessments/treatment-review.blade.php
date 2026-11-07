@extends('layouts.toolkit')

@section('title')
Residual Risk Review | NACSA Toolkit
@endsection
@section('page-label', 'Assessments')
@section('page-class', ' assessment-workspace')

@section('content')

<div class="page-heading"><div><h1>Residual Risk Review #{{ $treatment->id }}</h1></div></div>
@include('partials.assessment-navigation', ['activeTab' => 'treatments'])


    @if (!$isCurrentTreatment)
        <section><p>This plan belongs to assessment revision {{ $treatment->assessment_revision ?? 'unknown' }}.
            It is historical and view only. Its results do not approve the current revision.</p></section>
    @endif

    

    

    @include('partials.assessment-context')

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
                            <th>Inherent Risk</th>
                            <th>Expected Risk</th>
                            <th>Residual risk</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th>Score</th>
                            <td>{{ $baseline['result']['score'] ?? 'Unavailable' }}</td>
                            <td>{{ $baseline['expected_result']['score'] ?? 'Not assessed' }}</td>
                            <td>{{ $treatment->residual_result['score'] }}</td>
                        </tr>
                        <tr>
                            <th>Risk label</th>
                            <td>@include('partials.risk-badge', ['riskResult' => $baseline['result'] ?? [], 'riskProfile' => $assessment->profile])</td>
                            <td>@include('partials.risk-badge', ['riskResult' => $baseline['expected_result'] ?? [], 'riskProfile' => $assessment->profile])</td>
                            <td>@include('partials.risk-badge', ['riskResult' => $treatment->residual_result ?? [], 'riskProfile' => $assessment->profile])</td>
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

    @include('partials.residual-decision')

@endsection
