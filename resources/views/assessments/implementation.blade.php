@extends('layouts.toolkit')

@section('title')
Treatment Implementation | NACSA Toolkit
@endsection
@section('page-label', 'Assessments')
@section('page-class', ' assessment-workspace')

@section('content')

<div class="page-heading"><div><h1>Treatment Implementation #{{ $treatment->id }}</h1></div></div>
@include('partials.assessment-navigation', ['activeTab' => 'treatments'])


    @if (!$isCurrentTreatment)
        <section><p>This plan belongs to assessment revision {{ $treatment->assessment_revision ?? 'unknown' }}.
            It is historical and view only. Its results do not approve the current revision.</p></section>
    @endif

    

    

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

    @php
        $maximum = $assessment->profile === 'nacsa-5-2024' ? 5 : 3;

        $scoreLabels = [
            'c' => 'Confidentiality',
            'i' => 'Integrity',
            'a' => 'Availability',
            'likelihood' => 'Likelihood',
        ];

        if ($assessment->profile === 'usim-2026') {
            $scoreLabels += [
                'operations' => 'Operations impact',
                'financial' => 'Financial impact',
                'reputation' => 'Reputation impact',
            ];
        } else {
            $scoreLabels['loss'] = 'Loss';
        }
    @endphp

    <section>
        <h2>Planned controls or actions</h2>
        <p class="text">{{ $treatment->planned_controls ?? 'Not provided.' }}</p>
    </section>

    @if ($treatment->implemented_at !== null)
        <section>
            <h2>Recorded implementation</h2>

            <p>Recorded at: {{ $treatment->implemented_at }}</p>

            <h3>Evidence</h3>
            <p class="text">{{ $treatment->implementation_evidence }}</p>

            <h3>Risk comparison</h3>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Result</th>
                            <th>Inherent Risk</th>
                            <th>Residual risk</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th>Score</th>
                            <td>{{ $baseline['result']['score'] ?? 'Unavailable' }}</td>
                            <td>{{ $treatment->residual_result['score'] ?? 'Unavailable' }}</td>
                        </tr>
                        <tr>
                            <th>Risk label</th>
                            <td>@include('partials.risk-badge', ['riskResult' => $baseline['result'] ?? [], 'riskProfile' => $assessment->profile])</td>
                            <td>@include('partials.risk-badge', ['riskResult' => $treatment->residual_result ?? [], 'riskProfile' => $assessment->profile])</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <h3>Saved scoring inputs</h3>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Input</th>
                            <th>Inherent Risk</th>
                            <th>Residual risk</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($scoreLabels as $key => $label)
                            <tr>
                                <th>{{ $label }}</th>
                                <td>{{ $baseline['inputs'][$key] ?? 'Unavailable' }}</td>
                                <td>{{ $treatment->residual_inputs[$key] ?? 'Unavailable' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

          <p>
    <a class="secondary-action" href="{{ route('assessments.treatment-review', [
        'assessment' => $assessment->id,
        'treatment' => $treatment->id,
    ]) }}">
        Verification Details
    </a>
</p>
        </section>
    @else
        <section>
            <h2>Record implementation and residual risk</h2>

            @if (!$isCurrentTreatment)
                <p>This historical plan cannot be changed. Create a new plan for the verified current revision.</p>
            @elseif ($assessment->asset_deleted_at !== null)
                <p>This asset is archived. Implementation cannot be recorded.</p>
            @elseif ($assessment->status !== 'verified')
                <p>The assessment must be verified first.</p>
            @elseif (!$canManage)
                <p>
                    You have view-only access.
                    An Admin or Assessor department assignment is required
                    to record implementation.
                </p>
            @else
                <form method="POST"
                      action="{{ route('assessments.implementation.store', [
                          'assessment' => $assessment->id,
                          'treatment' => $treatment->id,
                      ]) }}">
                    @csrf

                    <input type="hidden"
                           name="revision"
                           value="{{ $assessment->revision }}">

                    <label>
                        Implementation evidence
                        <textarea name="implementation_evidence"
                                  maxlength="10000"
                                  required>{{ old('implementation_evidence') }}</textarea>
                    </label>

                    <p>
                        Describe what was completed, how it was checked,
                        and any supporting evidence references.
                    </p>

                    <h3>Residual Risk Scoring</h3>

                    <p>
                        Enter whole numbers from 1 to {{ $maximum }}.
                        Assess the remaining risk after existing controls and this plan’s implemented actions. Values start with the assessment inputs; review every value.
                        Change them only where the implemented controls
                        justify a different rating.
                    </p>

                    <div class="grid">
                        @foreach ($scoreLabels as $key => $label)
                            <label>
                                {{ $label }}
                                <input type="number"
                                       name="{{ $key }}"
                                       value="{{ old($key, $assessment->inputs[$key] ?? '') }}"
                                       min="1"
                                       max="{{ $maximum }}"
                                       step="1"
                                       required>
                            </label>
                        @endforeach
                    </div>

                    <p>
                        Saving records the implementation time and calculates
                        residual risk. This record cannot be edited through
                        this form after saving.
                    </p>
                    <button type="submit">
                        Save implementation and residual risk
                    </button>
                </form>
            @endif
        </section>
    @endif

@endsection
