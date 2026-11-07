@php
    $expectedInputs = \App\Services\ExpectedRisk::decode($assessment->expected_inputs ?? null);
    $expectedResult = \App\Services\ExpectedRisk::decode($assessment->expected_result ?? null);
@endphp
<section>
    <h2>Risk Scoring Comparison</h2>
    @foreach ($treatments->isEmpty() ? [null] : $treatments as $treatment)
        @if ($treatment)<h3>Treatment Plan {{ $treatment->id }}</h3>@endif
        <div class="table-wrap"><table>
            <thead><tr><th scope="col">Scoring Input</th><th scope="col">Inherent Risk</th><th scope="col">Expected Risk</th><th scope="col">Residual Risk</th></tr></thead>
            <tbody>
                @foreach (['c' => 'Confidentiality', 'i' => 'Integrity', 'a' => 'Availability', 'likelihood' => 'Likelihood', 'loss' => 'Loss', 'operations' => 'Operations Impact', 'financial' => 'Financial Impact', 'reputation' => 'Reputation Impact'] as $key => $label)
                    @if (array_key_exists($key, $assessment->inputs))
                        <tr><th scope="row">{{ $label }}</th><td>{{ $assessment->inputs[$key] }}</td><td>{{ $expectedInputs[$key] ?? 'Not assessed' }}</td><td>{{ $treatment->residual_inputs[$key] ?? 'Not assessed' }}</td></tr>
                    @endif
                @endforeach
                <tr><th scope="row">Risk Score</th><td>{{ $assessment->result['score'] ?? 'Not assessed' }}</td><td>{{ $expectedResult['score'] ?? 'Not assessed' }}</td><td>{{ $treatment->residual_result['score'] ?? 'Not assessed' }}</td></tr>
                <tr><th scope="row">Risk Level</th><td>@include('partials.risk-badge', ['riskResult' => $assessment->result, 'riskProfile' => $assessment->profile])</td><td>@include('partials.risk-badge', ['riskResult' => $expectedResult, 'riskProfile' => $assessment->profile])</td><td>@include('partials.risk-badge', ['riskResult' => $treatment->residual_result ?? [], 'riskProfile' => $assessment->profile])</td></tr>
            </tbody>
        </table></div>
    @endforeach
</section>
