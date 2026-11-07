<section aria-labelledby="risk-comparison-heading">
    <h2 id="risk-comparison-heading">Inherent, Expected and Residual Risk</h2>
    <div class="table-wrap" tabindex="0" role="region" aria-label="Risk comparison">
        <table>
            <thead>
                <tr><th scope="col">Risk Stage</th><th scope="col">Score</th><th scope="col">Risk Level</th><th scope="col">Review Status</th></tr>
            </thead>
            <tbody>
                <tr>
                    <th scope="row">Inherent Risk</th>
                    <td class="risk-score">{{ $assessment->result['score'] ?? 'Not assessed' }}</td>
                    <td>@include('partials.risk-badge', ['riskResult' => $assessment->result, 'riskProfile' => $assessment->profile])</td>
                    <td>{{ ucfirst($assessment->status) }}</td>
                </tr>
                @php($expectedResult = \App\Services\ExpectedRisk::decode($assessment->expected_result ?? null))
                <tr>
                    <th scope="row">Expected Risk</th>
                    <td class="risk-score">{{ $expectedResult['score'] ?? 'Not assessed' }}</td>
                    <td>@include('partials.risk-badge', ['riskResult' => $expectedResult, 'riskProfile' => $assessment->profile])</td>
                    <td>{{ $expectedResult ? ucfirst($assessment->status) : 'Not assessed' }}</td>
                </tr>
                @forelse ($riskComparison as $comparison)
                    <tr>
                        <th scope="row">Residual Risk<small class="risk-plan">Treatment Plan {{ $comparison['plan_id'] }}</small></th>
                        <td class="risk-score">{{ $comparison['result']['score'] ?? 'Not assessed' }}</td>
                        <td>@include('partials.risk-badge', ['riskResult' => $comparison['result'] ?? [], 'riskProfile' => $assessment->profile])</td>
                        <td>{{ $comparison['status'] }}</td>
                    </tr>
                @empty
                    <tr><th scope="row">Residual Risk</th><td>Not assessed</td><td><span class="risk-badge risk-neutral">Not assessed</span></td><td>Not started</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
