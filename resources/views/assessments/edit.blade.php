@extends('layouts.toolkit')

@section('title')
Edit Assessment | NACSA Toolkit
@endsection
@section('page-label', 'Assessments')
@section('page-class', ' assessment-workspace')

@section('content')

<div class="page-heading"><div><h1>Edit assessment #{{ $assessment->id }}</h1></div></div>
@include('partials.assessment-navigation', ['activeTab' => 'overview'])


    

    <section>
        

        <p>
            Asset: <strong>{{ $assessment->asset_code }}</strong><br>
            Profile: {{ $assessment->profile }}<br>
            Current revision: {{ $assessment->revision }}
        </p>

        <div class="notice">
            <p>Saving creates a new draft revision and requires a fresh review.
                Earlier reviews and treatments remain as history. The asset and scoring profile stay the same.</p>
            <strong>Latest returned-review comment</strong>
            <p class="comment">{{ $reviewComment ?? 'No comment available.' }}</p>
        </div>

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

        <form method="POST"
              action="{{ route('assessments.update', ['assessment' => $assessment->id]) }}">
            @csrf
            @method('PUT')

            <input type="hidden"
                   name="revision"
                   value="{{ $assessment->revision }}">
            <input type="hidden" name="status" value="{{ $assessment->status }}">

            <div class="grid">
                <label>
                    Assessment date
                    <input type="date"
                           name="assessed_on"
                           value="{{ old('assessed_on', $assessment->assessed_on) }}"
                           required>
                </label>

                <label>
                    Risk owner
                    <input name="risk_owner"
                           value="{{ old('risk_owner', $assessment->risk_owner) }}"
                           maxlength="180"
                           required>
                </label>
            </div>

            <p>
                <label>
                    Threat
                    <textarea name="threat"
                              maxlength="5000"
                              required>{{ old('threat', $assessment->threat) }}</textarea>
                </label>
            </p>

            <p>
                <label>
                    Vulnerability
                    <textarea name="vulnerability"
                              maxlength="5000"
                              required>{{ old('vulnerability', $assessment->vulnerability) }}</textarea>
                </label>
            </p>

            <p>
                <label>
                    Existing controls (optional)
                    <textarea name="existing_controls"
                              maxlength="5000">{{ old('existing_controls', $assessment->existing_controls) }}</textarea>
                </label>
            </p>

            <h2>Inherent Risk Scoring</h2>
            <p>Rate the risk before considering any controls. Review every score, including values loaded from an older assessment.</p>
            <p>Enter whole numbers from 1 to {{ $maximum }}.</p>

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
                Saving recalculates the result and creates draft revision
                {{ $assessment->revision + 1 }}.
                You can then submit it for review.
            </p>


            <div class="actions">
                <button type="submit">Save changes as draft</button>

                <a href="{{ route('assessments.review', ['assessment' => $assessment->id]) }}">
                    Cancel
                </a>
            </div>
        </form>
    </section>

@endsection
