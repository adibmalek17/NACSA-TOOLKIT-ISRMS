@extends('layouts.toolkit')

@section('title')
Dashboard | NACSA Toolkit
@endsection
@section('page-label', 'Dashboard')
@section('page-class', 'dashboard-page')

@section('content')
@include('partials.dashboard-analytics-styles')
@php
    $user = auth()->user();

    $summaryCards = [
        [
            'label' => 'Active assets',
            'value' => $stats['active_assets'],
            'url' => route('assets.index'),
            'icon' => 'assets',
            'tone' => '',
        ],
        [
            'label' => 'Draft assessments',
            'value' => $stats['draft'],
            'url' => route('assessments.index', ['status' => 'draft']),
            'icon' => 'document',
            'tone' => '',
        ],
        [
            'label' => 'Submitted assessments',
            'value' => $stats['submitted'],
            'url' => route('assessments.index', ['status' => 'submitted']),
            'icon' => 'clock',
            'tone' => 'amber',
        ],
        [
            'label' => 'Verified assessments',
            'value' => $stats['verified'],
            'url' => route('assessments.index', ['status' => 'verified']),
            'icon' => 'check',
            'tone' => 'teal',
        ],
    ];

    $statusGroups = [
        ['key' => 'draft', 'label' => 'Draft', 'colour' => '#64748b'],
        ['key' => 'submitted', 'label' => 'Submitted', 'colour' => '#d49b13'],
        ['key' => 'returned', 'label' => 'Returned', 'colour' => '#c44b4b'],
        ['key' => 'verified', 'label' => 'Verified', 'colour' => '#087c72'],
    ];
    $assessmentTotal = array_sum(array_map(fn ($group) => (int) $stats[$group['key']], $statusGroups));
    $treatmentTotal = (int) $stats['treatment_total'];
    $treatmentStages = [
        ['label' => 'Awaiting Implementation', 'value' => (int) $stats['awaiting_implementation'], 'colour' => '#d49b13'],
        ['label' => 'Awaiting Verification', 'value' => (int) $stats['awaiting_residual_verification'], 'colour' => '#2457c5'],
        ['label' => 'Verified', 'value' => (int) $stats['treatment_verified'], 'colour' => '#16804a'],
    ];
    if ($stats['treatment_incomplete'] > 0) {
        $treatmentStages[] = ['label' => 'Residual Scores Required', 'value' => (int) $stats['treatment_incomplete'], 'colour' => '#64748b'];
    }
    $segmentOffset = 0;
@endphp

<div class="page-heading">
    <div><h1>Department Overview</h1></div>
    <a class="button" href="{{ route('assessments.index') }}">Open Assessments <svg aria-hidden="true"><use href="#icon-arrow"/></svg></a>
</div>

@if ($memberships->isEmpty())
    <div class="empty-notice" role="status">No department assigned.</div>
@endif

<div class="metrics" aria-label="Department summary">
    @foreach ($summaryCards as $card)
        <a class="metric" href="{{ $card['url'] }}">
            <div class="metric-top">
                <h2>{{ $card['label'] }}</h2>
                <span class="metric-icon {{ $card['tone'] }}"><svg aria-hidden="true"><use href="#icon-{{ $card['icon'] }}"/></svg></span>
            </div>
            <p class="metric-number">{{ number_format($card['value']) }}</p>
        </a>
    @endforeach
</div>

<div class="dashboard-analytics">
    <section class="analytics-panel" aria-labelledby="status-heading">
        <div class="analytics-heading"><h2 id="status-heading">Assessment Status</h2><span class="analytics-tag">Current Status</span></div>
        <div class="status-visual">
            <div class="status-ring">
                <svg viewBox="0 0 120 120" aria-hidden="true" focusable="false">
                    <circle cx="60" cy="60" r="48" fill="none" stroke="#e8eef2" stroke-width="12"/>
                    @foreach ($statusGroups as $group)
                        @php
                            $segment = $assessmentTotal > 0 ? $stats[$group['key']] / $assessmentTotal * 100 : 0;
                        @endphp
                        @if ($segment > 0)
                            <circle cx="60" cy="60" r="48" fill="none" stroke="{{ $group['colour'] }}" stroke-width="12" pathLength="100" stroke-dasharray="{{ $segment }} {{ 100 - $segment }}" stroke-dashoffset="{{ -$segmentOffset }}" transform="rotate(-90 60 60)"/>
                        @endif
                        @php $segmentOffset += $segment; @endphp
                    @endforeach
                </svg>
                <div class="ring-total"><strong>{{ number_format($assessmentTotal) }}</strong><span>Assessments</span></div>
            </div>
            <table class="status-legend">
                <thead><tr><th scope="col">Status</th><th scope="col">Count</th><th scope="col">Share</th></tr></thead>
                <tbody>
                    @foreach ($statusGroups as $group)
                        <tr>
                            <th scope="row"><a href="{{ route('assessments.index', ['status' => $group['key']]) }}"><span class="legend-dot" style="background:{{ $group['colour'] }}" aria-hidden="true"></span>{{ $group['label'] }}</a></th>
                            <td>{{ number_format($stats[$group['key']]) }}</td>
                            <td>{{ $assessmentTotal > 0 ? number_format($stats[$group['key']] / $assessmentTotal * 100, 1) . '%' : '0%' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($assessmentTotal === 0)<p class="analytics-empty">No assessments recorded.</p>@endif
    </section>

    <section class="analytics-panel" aria-labelledby="treatment-progress-heading">
        <div class="analytics-heading"><h2 id="treatment-progress-heading">Treatment Progress</h2><span class="analytics-tag">Current Revision</span></div>
        <p class="risk-population"><strong>{{ number_format($treatmentTotal) }}</strong> Treatment plans</p>
        <div class="work-chart">
            @foreach ($treatmentStages as $stage)
                @php($share = $treatmentTotal > 0 ? $stage['value'] / $treatmentTotal * 100 : 0)
                <div class="work-bar-row">
                    <div class="work-bar-label"><span>{{ $stage['label'] }}</span><span><strong>{{ number_format($stage['value']) }}</strong><small class="risk-share">{{ number_format($share, 1) }}%</small></span></div>
                    <div class="work-track" aria-hidden="true"><span style="width:{{ $share }}%; background:{{ $stage['colour'] }}"></span></div>
                </div>
            @endforeach
        </div>
        @if ($treatmentTotal === 0)<p class="analytics-empty">No current treatment plans.</p>@endif
    </section>
</div>

@include('partials.dashboard-risk-analytics')

<section class="analytics-panel department-access" aria-labelledby="access-heading">
    <div class="analytics-heading"><h2 id="access-heading">Department Access</h2></div>
    <div class="dashboard-departments">
        @forelse ($memberships as $membership)
            <div class="dashboard-department"><h3>{{ $membership->name }}</h3><span class="role-badge">{{ ucfirst($membership->role) }}</span></div>
        @empty
            <p class="analytics-empty">No department assigned.</p>
        @endforelse
    </div>
</section>
@endsection
