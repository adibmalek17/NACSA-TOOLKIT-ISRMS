@php
    $expectedSaved = \App\Services\ExpectedRisk::decode($assessment->expected_inputs ?? null);
@endphp
<div class="expected-risk-fields" data-expected-risk data-profile="{{ $assessment->profile ?? '' }}">
    @if (isset($assessment))
        <h2>Expected Risk Scoring</h2>
    @else
        <h3>Expected Risk Scoring</h3>
    @endif
    <p class="hint">Rate the expected risk after planned controls.</p>
    <p class="hint expected-score-hint" data-expected-hint>Enter whole numbers for the selected scoring profile.</p>
    <div class="grid">
        @foreach (['c'=>'Confidentiality', 'i'=>'Integrity', 'a'=>'Availability', 'likelihood'=>'Likelihood', 'loss'=>'Loss', 'operations'=>'Operations Impact', 'financial'=>'Financial Impact', 'reputation'=>'Reputation Impact'] as $key => $label)
            <label data-expected-key="{{ $key }}">{{ $label }}
                <input type="number" name="expected[{{ $key }}]" min="1" max="5" step="1"
                    value="{{ old('expected.'.$key, $expectedSaved[$key] ?? '') }}">
            </label>
        @endforeach
    </div>
    <noscript><p>Enter the inputs for your selected profile. NACSA uses Loss; USIM uses Operations, Financial and Reputation.</p></noscript>
</div>
@once
<style>
.expected-risk-fields { margin-top:28px; }
.expected-score-hint { padding:12px 16px; border-left:3px solid #bccbd3; background:#f3f6f8; }
</style>
<script src="{{ asset('js/expected-risk.js') }}?v=3" defer></script>
@endonce
