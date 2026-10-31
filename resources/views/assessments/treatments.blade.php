@extends('layouts.toolkit')

@section('title')
Treatment Plans | NACSA Toolkit
@endsection
@section('page-label', 'Assessments')
@section('page-class', '')

@section('content')

<div class="page-heading"><div><h1>Treatment plans — Assessment #{{ $assessment->id }}</h1></div></div>
@include('partials.assessment-navigation', ['activeTab' => 'treatments'])


    

    

    <p>
        Asset: <strong>{{ $assessment->asset_code }}</strong><br>
        Assessment status: <strong>{{ ucfirst($assessment->status) }}</strong><br>
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
                        Start date — optional
                        <input type="date"
                               name="starts_on"
                               value="{{ old('starts_on') }}">
                    </label>

                    <label>
                        Due date — optional
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
                        Planned controls or actions — optional
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
            <article>
                <h3>
                    Plan #{{ $treatment->id }} — Revision {{ $treatment->assessment_revision ?? 'unknown' }}
                    — {{ $options[$treatment->option] ?? ucfirst($treatment->option) }}
                </h3>

                <p>
                    <strong>Responsible person:</strong>
                    {{ $treatment->responsible_person }}<br>

                    <strong>Start date:</strong>
                    {{ $treatment->starts_on ?? 'Not set' }}<br>

                    <strong>Due date:</strong>
                    {{ $treatment->due_on ?? 'Not set' }}<br>

                    <strong>Implementation:</strong>
                    {{ $treatment->implemented_at ? 'Recorded' : 'Not yet recorded' }}
                </p>

                @if ((int) $treatment->assessment_revision !== (int) $assessment->revision)
                    <p>Historical plan — view only. A new plan is required for the current revision.</p>
                @endif

                <h4>Rationale</h4>
                <p class="text">{{ $treatment->rationale }}</p>

                <h4>Planned controls or actions</h4>
                <p class="text">{{ $treatment->planned_controls ?? 'Not provided.' }}</p>

                <p>
                    <a href="{{ route('assessments.implementation', [
                        'assessment' => $assessment->id,
                        'treatment' => $treatment->id,
                    ]) }}">
                        View implementation and residual risk
                    </a>
                </p>
            </article>
        @empty
            <p>No treatment plans have been added.</p>
        @endforelse
    </section>

@endsection
