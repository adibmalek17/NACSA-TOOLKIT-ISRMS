<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Treatment Plans | NACSA Toolkit</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 24px;
            background: #f4f6f8;
            color: #183540;
            font-family: "Segoe UI", sans-serif;
        }

        main { max-width: 1100px; margin: auto; }

        section {
            margin-top: 24px;
            padding: 28px;
            background: white;
            border-radius: 12px;
        }

        a { color: #087c72; }
        p { line-height: 1.6; }

        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        label { display: block; font-weight: 600; }

        input, select, textarea {
            display: block;
            width: 100%;
            margin-top: 8px;
            padding: 11px;
            border: 1px solid #cbd8dd;
            border-radius: 6px;
            font: inherit;
        }

        textarea { min-height: 110px; }

        button {
            padding: 12px 24px;
            border: 0;
            border-radius: 7px;
            background: #087c72;
            color: white;
            font: inherit;
            cursor: pointer;
        }

        button:hover { background: #06675f; }

        .success, .errors {
            padding: 16px;
            margin-top: 20px;
            border-radius: 7px;
        }

        .success { background: #e5f5ee; color: #1e7251; }
        .errors { background: #fff0ee; color: #a13024; }

        .text {
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        article + article {
            margin-top: 28px;
            padding-top: 20px;
            border-top: 1px solid #e0e7eb;
        }

        input:focus-visible, select:focus-visible,
        textarea:focus-visible, button:focus-visible,
        a:focus-visible {
            outline: 3px solid #55baa9;
            outline-offset: 3px;
        }

        @media (max-width: 650px) {
            body { padding: 12px; }
            section { padding: 18px; }
            .grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<main>
    <p>
        <a href="{{ route('assessments.show', ['assessment' => $assessment->id]) }}">
            ← Assessment details
        </a>
        ·
        <a href="{{ route('assessments.review', ['assessment' => $assessment->id]) }}">
            Review workflow
        </a>
        ·
        <a href="{{ route('dashboard') }}">Dashboard</a>
    </p>

    <h1>Treatment plans — Assessment #{{ $assessment->id }}</h1>

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
</main>
</body>
</html>