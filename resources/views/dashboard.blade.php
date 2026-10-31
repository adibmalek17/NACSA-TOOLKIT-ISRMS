@extends('layouts.toolkit')

@section('title')
Dashboard | NACSA Toolkit
@endsection
@section('page-label', 'Dashboard')
@section('page-class', '')

@section('content')
@php
    $user = auth()->user();

    $summaryCards = [
        [
            'label' => 'Active assets',
            'value' => $stats['active_assets'],
            'caption' => 'In your assigned departments',
            'url' => route('assets.index'),
            'icon' => 'assets',
            'tone' => '',
        ],
        [
            'label' => 'Draft assessments',
            'value' => $stats['draft'],
            'caption' => 'Not yet submitted',
            'url' => route('assessments.index', ['status' => 'draft']),
            'icon' => 'document',
            'tone' => '',
        ],
        [
            'label' => 'Submitted assessments',
            'value' => $stats['submitted'],
            'caption' => 'Awaiting a review decision',
            'url' => route('assessments.index', ['status' => 'submitted']),
            'icon' => 'clock',
            'tone' => 'amber',
        ],
        [
            'label' => 'Verified assessments',
            'value' => $stats['verified'],
            'caption' => 'Assessment review completed',
            'url' => route('assessments.index', ['status' => 'verified']),
            'icon' => 'check',
            'tone' => 'teal',
        ],
    ];
@endphp
        <div class="page-heading">
            <div>
                <p class="eyebrow">Your risk workspace</p>
                <h1>Department overview</h1>
                <p>Keep track of your assets, assessments and next steps.</p>
            </div>

            <a class="button" href="{{ route('assessments.index') }}">
                Open assessments
                <svg aria-hidden="true"><use href="#icon-arrow"/></svg>
            </a>
        </div>

        @if ($memberships->isEmpty())
            <div class="empty-notice" role="status">
                <strong>No department assigned yet.</strong>
                Ask your administrator to assign a department so you can
                access its assets and assessments.
            </div>
        @endif

        <div class="metrics" aria-label="Department summary">
            @foreach ($summaryCards as $card)
                <a class="metric" href="{{ $card['url'] }}">
                    <div class="metric-top">
                        <h2>{{ $card['label'] }}</h2>
                        <span class="metric-icon {{ $card['tone'] }}">
                            <svg aria-hidden="true">
                                <use href="#icon-{{ $card['icon'] }}"/>
                            </svg>
                        </span>
                    </div>

                    <p class="metric-number">
                        {{ number_format($card['value']) }}
                    </p>
                    <p class="metric-caption">{{ $card['caption'] }}</p>
                </a>
            @endforeach
        </div>

        <div class="content-grid">
            <section class="panel" aria-labelledby="pending-heading">
                <div class="panel-heading">
                    <div>
                        <h2 id="pending-heading">Work awaiting action</h2>
                        <p>Pending work across your assigned departments.</p>
                    </div>
                    <span class="small-label">Department-wide</span>
                </div>

                <div class="task-list">
                    <div class="task">
                        <span class="task-symbol">
                            <svg aria-hidden="true"><use href="#icon-return"/></svg>
                        </span>
                        <div class="task-body">
                            <h3>
                                <a href="{{ route('assessments.index', ['status' => 'returned']) }}">
                                    Returned assessments
                                </a>
                            </h3>
                            <p>Review feedback and prepare corrections.</p>
                        </div>
                        <span class="task-count">
                            {{ number_format($stats['returned']) }}
                        </span>
                    </div>

                    <div class="task">
                        <span class="task-symbol blue">
                            <svg aria-hidden="true"><use href="#icon-document"/></svg>
                        </span>
                        <div class="task-body">
                            <h3>Awaiting implementation</h3>
                            <p>Treatment plans with implementation not yet recorded.</p>
                        </div>
                        <span class="task-count">
                            {{ number_format($stats['awaiting_implementation']) }}
                        </span>
                    </div>

                    <div class="task">
                        <span class="task-symbol teal">
                            <svg aria-hidden="true"><use href="#icon-shield"/></svg>
                        </span>
                        <div class="task-body">
                            <h3>Awaiting residual verification</h3>
                            <p>Recorded residual results awaiting verification.</p>
                        </div>
                        <span class="task-count">
                            {{ number_format($stats['awaiting_residual_verification']) }}
                        </span>
                    </div>
                </div>

                
            </section>

            <section class="panel" aria-labelledby="access-heading">
                <div class="panel-heading">
                    <div>
                        <h2 id="access-heading">Your department access</h2>
                        <p>Your assigned departments and roles.</p>
                    </div>
                    <svg aria-hidden="true"><use href="#icon-users"/></svg>
                </div>

                <div class="departments">
                    @forelse ($memberships as $membership)
                        <div class="department">
                            <div>
                                <h3>{{ $membership->name }}</h3>
                                <p>Department membership</p>
                            </div>
                            <span class="role-badge">
                                {{ ucfirst($membership->role) }}
                            </span>
                        </div>
                    @empty
                        <div class="department">
                            <p>No department memberships yet.</p>
                        </div>
                    @endforelse
                </div>

                <div class="access-note">
                    Your role determines which actions you can perform.
                    Dashboard counts include work by other department members.
                </div>
            </section>
        </div>

        

        
    
@endsection
