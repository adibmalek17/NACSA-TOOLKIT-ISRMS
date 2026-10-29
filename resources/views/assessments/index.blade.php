<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Risk Assessments | NACSA Toolkit</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 24px;
            background: #f4f6f8;
            color: #183540;
            font-family: "Segoe UI", sans-serif;
        }

        main { max-width: 1200px; margin: auto; }

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

        input, select, textarea {
            display: block;
            width: 100%;
            margin-top: 8px;
            padding: 11px;
            border: 1px solid #cbd8dd;
            border-radius: 6px;
            font: inherit;
        }

        textarea { min-height: 95px; resize: vertical; }

        button {
            margin-top: 20px;
            padding: 12px 24px;
            border: 0;
            border-radius: 7px;
            background: #087c72;
            color: white;
            font: inherit;
            cursor: pointer;
        }

        :focus-visible {
            outline: 3px solid #55baa9;
            outline-offset: 2px;
        }

        .success, .errors {
            padding: 15px;
            border-radius: 7px;
            margin-bottom: 20px;
        }

        .success { background: #e5f5ee; color: #1e7251; }
        .errors { background: #fff0ee; color: #a13024; }
        .hint { color: #526974; line-height: 1.6; }
        .table-wrap { overflow-x: auto; }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th, td {
            padding: 13px;
            border-bottom: 1px solid #e0e7eb;
            vertical-align: top;
        }

        th { background: #f4f6f8; }

        .pagination {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 20px;
        }

        .filter-actions {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 20px;
        }

        .filter-actions button { margin-top: 0; }

        .excel-upload { margin: 24px 0; padding: 20px; border: 1px solid #cbd8dd; border-radius: 8px; }
        .excel-upload legend { font-weight: 600; }
        button:disabled { opacity: 0.65; cursor: wait; }
        [hidden] { display: none !important; }

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
        <a href="{{ route('dashboard') }}">← Dashboard</a>
        ·
        <a href="{{ route('assets.index') }}">Asset register</a>
    </p>

    <h1>Risk assessments</h1>
    <p>Create and view assessments for your assigned departments.</p>


    <section>
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

        @if ($canCreate && $assets->isNotEmpty())
            <h2 id="create-assessment">Create an assessment</h2>

            <form id="assessment-form" method="POST" action="{{ route('assessments.store') }}"
                  data-excel-fill-url="{{ route('assessments.import.fill') }}"
                  data-excel-template-url="{{ route('assessments.import.template') }}">
                @csrf

                <div class="grid">
                    <label>
                        Asset
                        <select name="risk_asset_id" required>
                            <option value="">Select an asset</option>
                            @foreach ($assets as $asset)
                                <option
                                    value="{{ $asset->id }}"
                                    @selected(old('risk_asset_id') == $asset->id)
                                >
                                    {{ $asset->code }} — {{ $asset->name }}
                                    ({{ $asset->department_name }})
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label>
                        Scoring profile
                        <select id="profile" name="profile" required>
                            @foreach ($profiles as $profile)
                                <option
                                    value="{{ $profile }}"
                                    @selected(
                                        old('profile', 'nacsa-3-2024') === $profile
                                    )
                                >
                                    {{ $profile }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                </div>

                <fieldset class="excel-upload">
                    <legend>Fill from Excel — optional</legend>
                    <p class="hint">Choose your asset and scoring profile above, then select an Excel file. You can edit all values before saving.</p>
                    <p><a id="excel-template-link" href="{{ route('assessments.import.template', ['profile' => old('profile', 'nacsa-3-2024')]) }}">Download example for selected profile</a></p>
                    <label for="assessment-workbook">Excel file (.xlsx, up to 2 MB)</label>
                    <input id="assessment-workbook" type="file" accept=".xlsx" aria-describedby="excel-fill-note">
                    <p class="hint" id="excel-fill-note">Selecting a file fills one matching assessment row. It replaces the values below, including blank cells.</p>
                    <button id="excel-fill-button" type="button">Fill form from Excel</button>
                    <p id="excel-fill-status" role="status" aria-live="polite" hidden></p>
                    <div id="excel-fill-errors" class="errors" role="alert" hidden></div>
                    <noscript><p>Enable JavaScript to fill from Excel. You can still enter the assessment manually.</p></noscript>
                </fieldset>

                <div class="grid">
                    <label>
                        Assessment date
                        <input
                            type="date"
                            name="assessed_on"
                            value="{{ old('assessed_on', now()->toDateString()) }}"
                            required
                        >
                    </label>

                    <label>
                        Risk owner
                        <input
                            name="risk_owner"
                            value="{{ old('risk_owner') }}"
                            maxlength="180"
                            required
                        >
                    </label>

                    <label>
                        Threat
                        <textarea
                            name="threat"
                            maxlength="5000"
                            required
                        >{{ old('threat') }}</textarea>
                    </label>

                    <label>
                        Vulnerability
                        <textarea
                            name="vulnerability"
                            maxlength="5000"
                            required
                        >{{ old('vulnerability') }}</textarea>
                    </label>
                </div>

                <p>
                    <label>
                        Existing controls — optional
                        <textarea
                            name="existing_controls"
                            maxlength="5000"
                        >{{ old('existing_controls') }}</textarea>
                    </label>
                </p>

                <h3>Scoring inputs</h3>

                <p class="hint" id="score-hint">
                    Use 1–3 for nacsa-3-2024 and usim-2026.
                    Use 1–5 for nacsa-5-2024.
                    NACSA profiles use Loss; USIM uses Operations,
                    Financial and Reputation impacts.
                </p>

                <div class="grid">
                    @foreach ([
                        'c' => 'Confidentiality',
                        'i' => 'Integrity',
                        'a' => 'Availability',
                        'likelihood' => 'Likelihood',
                    ] as $key => $label)
                        <label>
                            {{ $label }}
                            <input
                                class="score-input"
                                type="number"
                                name="{{ $key }}"
                                value="{{ old($key, 1) }}"
                                min="1"
                                max="5"
                                step="1"
                                required
                            >
                        </label>
                    @endforeach

                    <label id="loss-field">
                        Loss
                        <input
                            class="score-input"
                            type="number"
                            name="loss"
                            value="{{ old('loss', 1) }}"
                            min="1"
                            max="5"
                            step="1"
                        >
                    </label>
                </div>

                <div id="usim-fields">
                    <h3>USIM impact inputs</h3>

                    <div class="grid">
                        @foreach ([
                            'operations' => 'Operations impact',
                            'financial' => 'Financial impact',
                            'reputation' => 'Reputation impact',
                        ] as $key => $label)
                            <label>
                                {{ $label }}
                                <input
                                    type="number"
                                    name="{{ $key }}"
                                    value="{{ old($key, 1) }}"
                                    min="1"
                                    max="3"
                                    step="1"
                                >
                            </label>
                        @endforeach
                    </div>
                </div>

                <p class="hint">
                    Saving calculates the result and creates a draft assessment.
                </p>

                <button type="submit">Calculate and save draft</button>
            </form>
        @elseif ($canCreate)
            <p>
                Register an active asset in your department before creating
                an assessment.
            </p>
            <a href="{{ route('assets.index') }}">Open asset register</a>
        @else
            <p>
                You have view-only access. An Admin or Assessor department
                assignment is required to create assessments.
            </p>
        @endif
    </section>

    <section id="saved-assessments">
        <h2>Saved assessments</h2>

        <form method="GET" action="{{ route('assessments.index') }}#saved-assessments">
            <div class="grid">
                <label>
                    Search asset code or name
                    <input type="search" name="q"
                           value="{{ $filters['q'] }}"
                           maxlength="180"
                           placeholder="For example: DEV-001 or Test Server">
                </label>

                <label>
                    Assessment status
                    <select name="status">
                        <option value="">All statuses</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}"
                                    @selected($filters['status'] === $status)>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <label>
                    Scoring profile
                    <select name="filter_profile">
                        <option value="">All profiles</option>
                        @foreach ($profiles as $filterProfile)
                            <option value="{{ $filterProfile }}"
                                    @selected($filters['filter_profile'] === $filterProfile)>
                                {{ $filterProfile }}
                            </option>
                        @endforeach
                    </select>
                </label>
            </div>

            <div class="filter-actions">
                <button type="submit">Apply filters</button>
                <a href="{{ route('assessments.index') }}#saved-assessments">Reset filters</a>
                <button type="submit" formaction="{{ route('assessments.export') }}">
                    Export CSV
                </button>
            </div>
        </form>

        <p class="hint" role="status">
            {{ $assessments->total() }} assessment(s) found in your assigned departments.
        </p>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Asset</th>
                        <th>Department</th>
                        <th>Date</th>
                        <th>Profile</th>
                        <th>Score</th>
                        <th>Risk level</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($assessments as $assessment)
                        <tr>
                            <td>
                                <a href="{{ route('assessments.show', ['assessment' => $assessment->id]) }}">
                                    View #{{ $assessment->id }}
                                </a>
                            </td>
                            <td>
                                <strong>{{ $assessment->asset_code }}</strong>
                                <br>
                                {{ $assessment->asset_name }}
                            </td>
                            <td>{{ $assessment->department_name }}</td>
                            <td>{{ $assessment->assessed_on }}</td>
                            <td>{{ $assessment->profile }}</td>
                            <td>{{ $assessment->result['score'] ?? '—' }}</td>
                            <td>{{ $assessment->result['label'] ?? '—' }}</td>
                           <td>
    {{ ucfirst($assessment->status) }}
</td>
<td>
    @include('assessments.lifecycle-actions', [
        'assessment' => $assessment,
    ])
</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                @if ($filters['q'] !== '' || $filters['status'] !== '' || $filters['filter_profile'] !== '')
                                    No assessments match your filters. Try another search or reset the filters.
                                @else
                                    No assessments saved yet.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">
            @if ($assessments->previousPageUrl())
                <a href="{{ $assessments->previousPageUrl() }}">Previous</a>
            @endif

            <span>
                Page {{ $assessments->currentPage() }}
                of {{ $assessments->lastPage() }}
            </span>

            @if ($assessments->nextPageUrl())
                <a href="{{ $assessments->nextPageUrl() }}">Next</a>
            @endif
        </div>
    </section>
</main>

<script>
    const profileSelect = document.getElementById('profile');

    if (profileSelect) {
        function updateScoreFields() {
            const profile = profileSelect.value;
            const isUsim = profile === 'usim-2026';
            const max = profile === 'nacsa-5-2024' ? 5 : 3;

            document.querySelectorAll('.score-input').forEach(input => {
                input.max = String(max);
            });

            const lossField = document.getElementById('loss-field');
            const lossInput = lossField.querySelector('input');

            lossField.hidden = isUsim;
            lossInput.disabled = isUsim;
            lossInput.required = !isUsim;

            const usimFields = document.getElementById('usim-fields');
            usimFields.hidden = !isUsim;

            usimFields.querySelectorAll('input').forEach(input => {
                input.disabled = !isUsim;
                input.required = isUsim;
            });

            document.getElementById('score-hint').textContent =
                `Enter whole numbers from 1 to ${max} for this profile.`;
        }

        profileSelect.addEventListener('change', updateScoreFields);
        updateScoreFields();
    }
</script>
<script src="{{ asset('js/assessment-excel-fill.js') }}" defer></script>
</body>
</html>