@extends('layouts.toolkit')

@section('title')
Risk Assessments | NACSA Toolkit
@endsection
@section('page-label', 'Assessments')
@section('page-class', 'assessment-list-page assessment-workspace')

@section('content')
@php
    $user = auth()->user();
@endphp
        <div class="page-heading">
            <div>
                
                <h1>Risk assessments</h1>
                
            </div>
            <a class="button" href="#saved-assessments">Saved assessments <svg aria-hidden="true"><use href="#icon-arrow"/></svg></a>
        </div>
    <section class="assessment-panel">
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
                    <legend>Upload Excel</legend>
                    
                    <a id="excel-template-link" hidden aria-hidden="true" tabindex="-1" href="{{ route('assessments.import.template', ['profile' => old('profile', 'nacsa-3-2024')]) }}"></a>
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
                        Existing controls (optional)
                        <textarea
                            name="existing_controls"
                            maxlength="5000"
                        >{{ old('existing_controls') }}</textarea>
                    </label>
                </p>

                <h3>Inherent Risk Scoring</h3>
                <p class="hint">Rate the risk before any controls. Existing controls are recorded for context and must not reduce these scores.</p>

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

    
                @include('partials.expected-risk-fields')
                <button type="submit">Save Assessment</button>
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

    <section class="assessment-panel" id="saved-assessments">
        <h2>Saved assessments</h2>

        <form class="filter-form" method="GET" action="{{ route('assessments.index') }}#saved-assessments">
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

        <p class="hint results-count" role="status">
            {{ $assessments->total() }} assessment(s) found in your assigned departments.
        </p>

        <div class="table-wrap" role="region" aria-label="Saved assessments table" tabindex="0">
            <table>
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Asset</th>
                        <th scope="col">Department</th>
                        <th scope="col">Date</th>
                        <th scope="col">Profile</th>
                        <th scope="col">Inherent risk</th>
                        <th scope="col">Expected risk</th>
                        <th scope="col">Residual risk</th>
                        <th scope="col">Status</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($assessments as $assessment)
                        <tr>
                            <td>
                                #{{ $assessment->id }}
                            </td>
                            <td>
                                <a class="asset-assessment-link" href="{{ route('assessments.show', ['assessment' => $assessment->id]) }}" aria-label="Open assessment {{ $assessment->id }} for {{ $assessment->asset_code }}"><strong>{{ $assessment->asset_code }}</strong></a>
                                <br>
                                {{ $assessment->asset_name }}
                            </td>
                            <td>{{ $assessment->department_name }}</td>
                            <td>{{ $assessment->assessed_on }}</td>
                            <td>{{ $assessment->profile }}</td>
                            <td>
                                <strong>{{ $assessment->result['score'] ?? 'Not assessed' }}</strong>
                            </td>
                            <td><strong>{{ \App\Services\ExpectedRisk::decode($assessment->expected_result ?? null)['score'] ?? 'Not assessed' }}</strong></td>
                            <td>
                                @forelse ($riskComparisons->get($assessment->id, []) as $comparison)
                                    <div>Plan #{{ $comparison['plan_id'] }}: {{ $comparison['result']['score'] ?? 'Not assessed' }}
                                        @if ($comparison['result'] !== null)
                                            <br><small>{{ $comparison['status'] }}</small>
                                        @endif
                                    </div>
                                @empty
                                    Not assessed
                                @endforelse
                            </td>
                           <td>
    <span class="status-badge status-{{ $assessment->status }}">{{ ucfirst($assessment->status) }}</span>
</td>
<td>
    @include('assessments.lifecycle-actions', [
        'assessment' => $assessment,
    ])
</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10">
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
        
    
@endsection

@section('scripts')
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
<script src="{{ asset('js/assessment-excel-fill.js').'?v=expected-risk-1' }}" defer></script>
@endsection
