<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Assessment Activity | NACSA Toolkit</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 24px;
            background: #f4f6f8;
            color: #183540;
            font-family: "Segoe UI", sans-serif;
        }

        main {
            max-width: 1100px;
            margin: auto;
        }

        section {
            margin-top: 24px;
            padding: 28px;
            background: white;
            border-radius: 12px;
        }

        a { color: #087c72; }
        p { line-height: 1.7; }

        h1, td {
            overflow-wrap: anywhere;
        }

        .hint {
            color: #526974;
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th, td {
            padding: 14px;
            border-bottom: 1px solid #e0e7eb;
            vertical-align: top;
        }

        th {
            background: #f4f6f8;
        }

        .action-code {
            display: block;
            margin-top: 5px;
            color: #526974;
            font-size: 0.85rem;
        }

        .pagination {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 24px;
        }

        a:focus-visible {
            outline: 3px solid #55baa9;
            outline-offset: 3px;
        }

        @media (max-width: 650px) {
            body { padding: 12px; }
            section { padding: 18px; }
        }
    </style>
</head>

<body>
<main>
    <p>
        <a href="{{ route('assessments.show', ['assessment' => $assessment->id]) }}">
            ← Assessment details
        </a>
        ·
        <a href="{{ route('assessments.index') }}">Risk assessments</a>
        ·
        <a href="{{ route('dashboard') }}">Dashboard</a>
    </p>

    <h1>Activity history — Assessment #{{ $assessment->id }}</h1>

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
</main>
</body>
</html>