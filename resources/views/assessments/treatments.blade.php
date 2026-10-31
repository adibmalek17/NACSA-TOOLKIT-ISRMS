@extends('layouts.toolkit')

@section('title')
Treatment Plans | NACSA Toolkit
@endsection
@section('page-label', 'Assessments')
@section('page-class', ' assessment-workspace')

@section('content')

<div class="page-heading"><div><h1>Treatment plans for Assessment #{{ $assessment->id }}</h1></div></div>
@include('partials.assessment-navigation', ['activeTab' => 'treatments'])


    

    

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
        <h2>Add a treatment plan</h2>

        @if ($assessment->asset_deleted_at !== null)
            <p>This asset is archived. Existing treatment plans are view only.</p>
        @elseif ($assessment->status !== 'verified')
            <p>The assessment must be verified before adding treatment plans.</p>
        @elseif (!$canManage)
            <p>
                You can view treatment plans.
                An Admin or Assessor department assignment is required to add one.
            </p>
        @else
            <form method="POST"
                  action="{{ route('assessments.treatments.store', ['assessment' => $assessment->id]) }}">
                @csrf

                <input type="hidden"
                       name="revision"
                       value="{{ $assessment->revision }}">

                <div class="grid">
                    <label>
                        Treatment option
                        <select name="option" required>
                            <option value="">Select an option</option>

                            @foreach ($options as $value => $label)
                                <option value="{{ $value }}"
                                        @selected(old('option') === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label>
                        Responsible person
                        <input name="responsible_person"
                               value="{{ old('responsible_person') }}"
                               maxlength="255"
                               required>
                    </label>

                    <label>
                        Start date (optional)
                        <input type="date"
                               name="starts_on"
                               value="{{ old('starts_on') }}">
                    </label>

                    <label>
                        Due date (optional)
                        <input type="date"
                               name="due_on"
                               value="{{ old('due_on') }}">
                    </label>
                </div>

                <p>
                    <label>
                        Rationale
                        <textarea name="rationale"
                                  maxlength="5000"
                                  required>{{ old('rationale') }}</textarea>
                    </label>
                </p>

                <p>
                    <label>
                        Planned controls or actions (optional)
                        <textarea name="planned_controls"
                                  maxlength="5000">{{ old('planned_controls') }}</textarea>
                    </label>
                </p>

                <button type="submit">Save treatment plan</button>
            </form>
        @endif
    </section>

    <section>
        <h2>Saved treatment plans</h2>

        @forelse ($treatments as $treatment)
            <article class="treatment-card">
                @php
                    $panel = $residualPanels->get($treatment->id);
                    $historical = (int) $treatment->assessment_revision !== (int) $assessment->revision;
                    $verified = $panel && $panel['review'] && $panel['review']->decision === 'verified';
                    $ready = $treatment->implemented_at !== null && $treatment->residual_result !== null;
                @endphp
                <div class="panel-heading">
                    <div><p class="eyebrow">{{ $options[$treatment->option] ?? ucfirst($treatment->option) }}</p><h3>Treatment Plan {{ $treatment->id }}</h3></div>
                    <span class="verification-badge {{ $historical ? 'verification-neutral' : ($verified ? 'verification-complete' : 'verification-pending') }}">{{ $historical ? 'Historical' : ($verified ? 'Verified' : ($ready ? 'Awaiting Verification' : 'Awaiting Implementation')) }}</span>
                </div>

                <dl class="plan-metadata">
                    <div><dt>Responsible Person</dt><dd>{{ $treatment->responsible_person }}</dd></div>
                    <div><dt>Start Date</dt><dd>{{ $treatment->starts_on ?? 'Not set' }}</dd></div>
                    <div><dt>Due Date</dt><dd>{{ $treatment->due_on ?? 'Not set' }}</dd></div>
                    <div><dt>Implementation</dt><dd>{{ $treatment->implemented_at ? 'Recorded' : 'Not recorded' }}</dd></div>
                </dl>

                @if ((int) $treatment->assessment_revision !== (int) $assessment->revision)
                    <p>Historical record. Revision {{ $treatment->assessment_revision ?? 'unknown' }}.</p>
                @endif

                <h4>Rationale</h4>
                <p class="text">{{ $treatment->rationale }}</p>

                <h4>Planned controls or actions</h4>
                <p class="text">{{ $treatment->planned_controls ?? 'Not provided.' }}</p>

                <div class="plan-actions">
                    <a class="secondary-action" href="{{ route('assessments.implementation', [
                        'assessment' => $assessment->id,
                        'treatment' => $treatment->id,
                    ]) }}">
                        {{ $historical || !$canManage || $assessment->status !== 'verified' || $assessment->asset_deleted_at !== null || $treatment->implemented_at ? 'View Implementation' : 'Record Implementation' }}
                    </a>
                <a class="secondary-action" href="{{ route('assessments.treatment-review', ['assessment' => $assessment->id, 'treatment' => $treatment->id]) }}">{{ $verified ? 'View Review Decision' : 'Verification Details' }}</a></div>

            </article>
        @empty
            <p>No treatment plans have been added.</p>
        @endforelse
    </section>

@endsection
