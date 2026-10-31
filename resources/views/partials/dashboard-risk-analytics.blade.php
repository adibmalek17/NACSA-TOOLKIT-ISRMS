@php
    $riskAnalytics = \App\Services\DashboardRiskAnalytics::forDepartments($memberships->pluck('id')->unique()->values()->all());
    $riskCharts = [
        ['key' => 'inherent', 'title' => 'Inherent Risk Levels', 'unit' => 'assessments', 'scope' => 'Active assets'],
        ['key' => 'residual', 'title' => 'Residual Risk Levels', 'unit' => 'implemented plans', 'scope' => 'Current revision'],
    ];
    $riskBands = [
        'high' => ['label' => 'High', 'colour' => '#b83242'],
        'medium' => ['label' => 'Medium', 'colour' => '#c29412'],
        'low' => ['label' => 'Low', 'colour' => '#16804a'],
        'neutral' => ['label' => 'Unclassified', 'colour' => '#64748b'],
    ];
@endphp
<div class="dashboard-analytics dashboard-risk-grid">
    @foreach ($riskCharts as $riskChart)
        @php
            $riskCounts = $riskAnalytics[$riskChart['key']];
            $riskTotal = array_sum($riskCounts);
        @endphp
        <section class="analytics-panel" aria-labelledby="{{ $riskChart['key'] }}-risk-heading">
            <div class="analytics-heading"><h2 id="{{ $riskChart['key'] }}-risk-heading">{{ $riskChart['title'] }}</h2><span class="analytics-tag">{{ $riskChart['scope'] }}</span></div>
            <p class="risk-population"><strong>{{ number_format($riskTotal) }}</strong> {{ $riskChart['unit'] }}</p>
            <div class="risk-distribution">
                @foreach ($riskBands as $band => $bandStyle)
                    @if ($band !== 'neutral' || $riskCounts[$band] > 0)
                        @php $riskShare = $riskTotal > 0 ? $riskCounts[$band] / $riskTotal * 100 : 0; @endphp
                        <div class="risk-distribution-row">
                            <div class="work-bar-label">
                                <span><span class="legend-dot" style="background:{{ $bandStyle['colour'] }}" aria-hidden="true"></span> {{ $bandStyle['label'] }}</span>
                                <span><strong>{{ number_format($riskCounts[$band]) }}</strong><small class="risk-share">{{ number_format($riskShare, 1) }}%</small></span>
                            </div>
                            <div class="work-track" aria-hidden="true"><span style="width:{{ $riskShare }}%; background:{{ $bandStyle['colour'] }}"></span></div>
                        </div>
                    @endif
                @endforeach
            </div>
            @if ($riskTotal === 0)
                <p class="analytics-empty">{{ $riskChart['key'] === 'inherent' ? 'No assessments for active assets.' : 'No residual results recorded.' }}</p>
            @endif
        </section>
    @endforeach
</div>
