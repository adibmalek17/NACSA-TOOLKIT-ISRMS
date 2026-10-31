<span class="risk-badge risk-{{ \App\Services\RiskPresentation::tone($riskResult ?? [], $riskProfile ?? null) }}">{{ $riskResult['label'] ?? 'Not assessed' }}</span>
