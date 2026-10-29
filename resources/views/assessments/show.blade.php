<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Assessment #{{ $assessment->id }} | NACSA Toolkit</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 24px;
            background: #f4f6f8;
            color: #183540;
            font-family: "Segoe UI", sans-serif;
        }

        main {
            max-width: 1100px;
            margin: auto;
        }

        section {
            margin-top: 24px;
            padding: 28px;
            background: white;
            border-radius: 12px;
        }

        a { color: #087c72; }

        a:focus-visible {
            outline: 3px solid #55baa9;
            outline-offset: 3px;
        }

        h1, h2, h3 { overflow-wrap: anywhere; }

        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }

        dl { margin: 0; }
        dt { font-weight: 600; }

        dd {
            margin: 6px 0 0;
            line-height: 1.6;
            overflow-wrap: anywhere;
        }

        .text {
            white-space: pre-wrap;
            overflow-wrap: anywhere;
            line-height: 1.7;
        }

        .hint {
            color: #526974;
            line-height: 1.6;
        }

        .notice {
            padding: 14px;
            background: #fff4d6;
            border-radius: 7px;
            line-height: 1.6;
        }

        .result {
            padding: 20px;
            background: #e5f5ee;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .result p { margin: 6px 0; }

        .score {
            font-size: 2rem;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #e0e7eb;
        }

        thead th { background: #f4f6f8; }

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
        <a href="{{ route('assessments.index') }}">← Risk assessments</a>
        ·
        <a href="{{ route('dashboard') }}">Dashboard</a>
    </p>

    <h1>Assessment #{{ $assessment->id }}</h1>

    <p>
    <a href="{{ route('assessments.history', ['assessment' => $assessment->id]) }}">
        Open activity history
    </a>
</p>

    <p>
        <a href="{{ route('assessments.report', ['assessment' => $assessment->id]) }}">
            Open printable report
        </a>
    </p>

    <p>
        <a href="{{ route('assessments.treatments', ['assessment' => $assessment->id]) }}">
            Open treatment plans
        </a>
    </p>

    <p>
        <a href="{{ route('assessments.review', ['assessment' => $assessment->id]) }}">
            Open review workflow
        </a>
    </p>

    <p>
        {{ $assessment->asset_code }} — {{ $assessment->asset_name }}
    </p>

    @if ($assessment->asset_deleted_at)
        <p class="notice">
            This asset is archived. Its saved assessment remains available
            for reference.
        </p>
    @endif

    <section>
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

    <section>
        <h2>Risk description</h2>

        <h3>Threat</h3>
        <p class="text">{{ $assessment->threat }}</p>

        <h3>Vulnerability</h3>
        <p class="text">{{ $assessment->vulnerability }}</p>

        <h3>Existing controls</h3>
        <p class="text">{{ $assessment->existing_controls ?: 'No controls recorded.' }}</p>
    </section>

    <section>
        <h2>Saved scoring inputs</h2>

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

    <section>
        <h2>Saved result</h2>

        <div class="result">
            <p>Risk score</p>
            <p class="score">{{ $assessment->result['score'] ?? '—' }}</p>
            <p>
                Risk level:
                <strong>{{ $assessment->result['label'] ?? '—' }}</strong>
            </p>
        </div>

        <dl class="grid">
            <div>
                <dt>CIA total</dt>
                <dd>{{ $assessment->result['ciaTotal'] ?? '—' }}</dd>
            </div>

            <div>
                <dt>Asset value</dt>
                <dd>{{ $assessment->result['asset'] ?? '—' }}</dd>
            </div>

            <div>
                <dt>Raw impact</dt>
                <dd>{{ $assessment->result['impactRaw'] ?? '—' }}</dd>
            </div>

            <div>
                <dt>Impact value</dt>
                <dd>{{ $assessment->result['impact'] ?? '—' }}</dd>
            </div>

            <div>
                <dt>Risk level number</dt>
                <dd>{{ $assessment->result['level'] ?? '—' }}</dd>
            </div>

            <div>
                <dt>Profile override applied</dt>
                <dd>
                    {{ ($assessment->result['override'] ?? false) ? 'Yes' : 'No' }}
                </dd>
            </div>
        </dl>

        <p class="hint">
            These are the results stored when this assessment was saved.
            Opening this page does not recalculate them.
        </p>
    </section>
</main>
</body>
</html>