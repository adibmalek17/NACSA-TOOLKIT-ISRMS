@extends('layouts.toolkit')

@section('title')
Assessment Activity | NACSA Toolkit
@endsection
@section('page-label', 'Assessments')
@section('page-class', '')

@section('content')

<div class="page-heading"><div><h1>Activity history — Assessment #{{ $assessment->id }}</h1></div></div>
@include('partials.assessment-navigation', ['activeTab' => 'history'])


    

    

    <section>
        <h2>Assessment summary</h2>

        <p>
            <strong>Asset:</strong>
            {{ $assessment->asset_code }} — {{ $assessment->asset_name }}
            <br>

            <strong>Department:</strong>
            {{ $assessment->department_name }}
            <br>

            <strong>Current status:</strong>
            {{ ucfirst($assessment->status) }}
            <br>

            <strong>Current revision:</strong>
            {{ $assessment->revision }}
        </p>
    </section>

    <section>
        <h2>Recorded activity</h2>

        <p class="hint">
            Latest activity appears first. This page shows audit events
            recorded for this assessment, including its treatment plans.
            Times are shown as stored by the application.
        </p>

        <p>{{ $events->total() }} event(s) recorded.</p>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th scope="col">Date and time</th>
                        <th scope="col">Action</th>
                        <th scope="col">Performed by</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($events as $event)
                        <tr>
                            <td>{{ $event->created_at }}</td>
                            <td>
                                {{ ucfirst(str_replace(['.', '_', '-'], ' ', $event->action)) }}

                                <code class="action-code">{{ $event->action }}</code>
                            </td>
                         <td>
    <strong>{{ $event->actor_name ?? 'Unavailable' }}</strong>

    @if ($event->actor_email)
        <span class="action-code">
            {{ $event->actor_email }}
        </span>
    @endif

    <span class="action-code">
        @if ($event->actor_id !== null)
            User ID: {{ $event->actor_id }}
        @else
            No actor recorded
        @endif
    </span>
</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">
                                No activity has been recorded for this assessment.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <nav class="pagination" aria-label="Activity history pages">
            @if ($events->previousPageUrl())
                <a href="{{ $events->previousPageUrl() }}">Previous</a>
            @endif

            <span>
                Page {{ $events->currentPage() }}
                of {{ $events->lastPage() }}
            </span>

            @if ($events->nextPageUrl())
                <a href="{{ $events->nextPageUrl() }}">Next</a>
            @endif
        </nav>
    </section>

@endsection
