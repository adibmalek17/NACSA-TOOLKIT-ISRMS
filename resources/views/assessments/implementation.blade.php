<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Treatment Implementation | NACSA Toolkit</title>

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

        input, textarea {
            display: block;
            width: 100%;
            margin-top: 8px;
            padding: 11px;
            border: 1px solid #cbd8dd;
            border-radius: 6px;
            font: inherit;
        }

        textarea { min-height: 150px; }

        button {
            padding: 12px 24px;
            background: #087c72;
            color: white;
            border: 0;
            border-radius: 7px;
            font: inherit;
            cursor: pointer;
        }

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

        .table-wrap { overflow-x: auto; }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #e0e7eb;
        }

        th { background: #f4f6f8; }

        input:focus-visible, textarea:focus-visible,
        button:focus-visible, a:focus-visible {
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
    @if (!$isCurrentTreatment)
        <section><p>This plan belongs to assessment revision {{ $treatment->assessment_revision ?? 'unknown' }}.
            It is historical and view only. Its results do not approve the current revision.</p></section>
    @endif

    <p>
        <a href="{{ route('assessments.treatments', ['assessment' => $assessment->id]) }}">
            ← Treatment plans
        </a>
        ·
        <a href="{{ route('assessments.show', ['assessment' => $assessment->id]) }}">
            Assessment details
        </a>
        ·
        <a href="{{ route('dashboard') }}">Dashboard</a>
    </p>

    <h1>Implementation — Plan #{{ $treatment->id }}</h1>

    <p>
        Assessment: #{{ $assessment->id }}<br>
        Asset: <strong>{{ $assessment->asset_code }}</strong><br>
        Profile: {{ $assessment->profile }}<br>
        Treatment option: {{ ucfirst($treatment->option) }}<br>
        Responsible person: {{ $treatment->responsible_person }}
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
                            <th>Verified assessment</th>
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
                            <td>{{ $baseline['result']['label'] ?? 'Unavailable' }}</td>
                            <td>{{ $treatment->residual_result['label'] ?? 'Unavailable' }}</td>
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
                            <th>Verified assessment</th>
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
    <a href="{{ route('assessments.treatment-review', [
        'assessment' => $assessment->id,
        'treatment' => $treatment->id,
    ]) }}">
        View verification status or review this residual risk
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

                    <h3>Residual scoring inputs</h3>

                    <p>
                        Enter whole numbers from 1 to {{ $maximum }}.
                        Values start with the verified assessment inputs.
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
</main>
</body>
</html>