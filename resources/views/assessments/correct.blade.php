<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Correct Assessment | NACSA Toolkit</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 24px;
            background: #f4f6f8;
            color: #183540;
            font-family: "Segoe UI", sans-serif;
        }

        main { max-width: 1000px; margin: auto; }

        section {
            margin-top: 24px;
            padding: 28px;
            background: white;
            border-radius: 12px;
        }

        a { color: #087c72; }

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

        .notice, .errors {
            padding: 18px;
            margin-bottom: 20px;
            border-radius: 7px;
        }

        .notice { background: #fff5da; }
        .errors { background: #fff0ee; color: #a13024; }
        .comment { white-space: pre-wrap; overflow-wrap: anywhere; }

        .actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 20px;
            margin-top: 24px;
        }

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
    <p>
        <a href="{{ route('assessments.review', ['assessment' => $assessment->id]) }}">
            ← Review workflow
        </a>
    </p>

    <section>
        <h1>Correct assessment #{{ $assessment->id }}</h1>

        <p>
            Asset: <strong>{{ $assessment->asset_code }}</strong><br>
            Profile: {{ $assessment->profile }}<br>
            Current revision: {{ $assessment->revision }}
        </p>

        <div class="notice">
            <strong>Reviewer’s comment</strong>
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
              action="{{ route('assessments.correct.update', ['assessment' => $assessment->id]) }}">
            @csrf
            @method('PUT')

            <input type="hidden"
                   name="revision"
                   value="{{ $assessment->revision }}">

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
                    Existing controls — optional
                    <textarea name="existing_controls"
                              maxlength="5000">{{ old('existing_controls', $assessment->existing_controls) }}</textarea>
                </label>
            </p>

            <h2>Scoring inputs</h2>
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
                <button type="submit">Save corrected draft</button>

                <a href="{{ route('assessments.review', ['assessment' => $assessment->id]) }}">
                    Cancel
                </a>
            </div>
        </form>
    </section>
</main>
</body>
</html>