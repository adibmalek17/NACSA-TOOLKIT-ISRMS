<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard | NACSA Toolkit</title>

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
            max-width: 1200px;
            margin: 24px auto;
        }

        header, section {
            padding: 28px;
            background: white;
            border-radius: 14px;
        }

        section { margin-top: 24px; }

        .brand {
            margin-top: 0;
            color: #087c72;
            font-weight: bold;
            letter-spacing: 2px;
        }

        h1, h2, h3 { overflow-wrap: anywhere; }
        p { line-height: 1.7; }
        a { color: #087c72; }

        .hint { color: #526974; }

        .header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .header-row h1 { margin: 8px 0; }

        .navigation {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 18px;
        }

        .navigation a {
            display: inline-block;
            padding: 11px 18px;
            border: 1px solid #087c72;
            border-radius: 7px;
            text-decoration: none;
            font-weight: 600;
        }

        .navigation a:hover { background: #e5f5ee; }

        .cards {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
        }

        .card {
            padding: 20px;
            border: 1px solid #dce6e9;
            border-radius: 10px;
            background: #f8fafb;
        }

        .card h3 {
            margin: 0;
            font-size: 1rem;
            line-height: 1.5;
        }

        .number {
            margin: 10px 0 0;
            font-size: 2.3rem;
            line-height: 1.2;
            font-weight: bold;
            color: #087c72;
        }

        .card.pending {
            background: #fff8e8;
            border-color: #ead8a8;
        }

        .card.pending .number { color: #805900; }

        .card.verified {
            background: #e5f5ee;
            border-color: #bfdfcf;
        }

        .departments {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .department {
            padding: 16px;
            border: 1px solid #dce6e9;
            border-radius: 8px;
        }

        .department p { margin: 0; }

        button {
            padding: 12px 24px;
            background: #087c72;
            color: white;
            border: none;
            border-radius: 7px;
            font: inherit;
            cursor: pointer;
        }

        button:hover { background: #06675f; }

        a:focus-visible, button:focus-visible {
            outline: 3px solid #55baa9;
            outline-offset: 3px;
        }

        @media (max-width: 950px) {
            .cards { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 600px) {
            body { padding: 12px; }
            main { margin: 12px auto; }
            header, section { padding: 20px; }

            .cards, .departments {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<main>
    <header>
        <p class="brand">NACSA TOOLKIT</p>

        <div class="header-row">
            <div>
                <h1>Welcome, {{ auth()->user()->name }}</h1>
                <p>Signed in as {{ auth()->user()->email }}.</p>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">Sign out</button>
            </form>
        </div>

        <nav class="navigation" aria-label="Main navigation">
            <a href="{{ route('assets.index') }}">Open asset register</a>
            <a href="{{ route('assessments.index') }}">Open risk assessments</a>
            <a href="{{ route('account.security') }}">Account security</a>
            @if (auth()->user()->is_system_admin)
                <a href="{{ route('admin.users.index') }}">User management</a>
            @endif
        </nav>
    </header>

    <section>
        <h2>Department overview</h2>

        <p class="hint">
            These counts cover your assigned departments.
            They show department activity, including work carried out by other members.
        </p>

        @if ($memberships->isEmpty())
            <p>
                No department has been assigned to your account.
                Your counts will remain zero until an assignment is added.
            </p>
        @endif

        <div class="cards">
            <article class="card">
                <h3>Active assets</h3>
                <p class="number">{{ number_format($stats['active_assets']) }}</p>
            </article>

            <article class="card">
                <h3>Draft assessments</h3>
                <p class="number">{{ number_format($stats['draft']) }}</p>
            </article>

            <article class="card pending">
                <h3>Submitted assessments</h3>
                <p class="number">{{ number_format($stats['submitted']) }}</p>
            </article>

            <article class="card pending">
                <h3>Returned assessments</h3>
                <p class="number">{{ number_format($stats['returned']) }}</p>
            </article>

            <article class="card verified">
                <h3>Verified assessments</h3>
                <p class="number">{{ number_format($stats['verified']) }}</p>
            </article>

            <article class="card pending">
                <h3>Plans awaiting implementation</h3>
                <p class="number">
                    {{ number_format($stats['awaiting_implementation']) }}
                </p>
            </article>

            <article class="card pending">
                <h3>Plans awaiting residual verification</h3>
                <p class="number">
                    {{ number_format($stats['awaiting_residual_verification']) }}
                </p>
            </article>
        </div>

        <p class="hint">
            Assessment counts use each assessment’s current status and include
            history for archived assets. Pending treatment counts include only
            active assets with verified assessments.
        </p>

        <p class="hint">
            A plan leaves the residual verification count when its residual
            result is verified for the current assessment revision.
        </p>
    </section>

    <section>
        <h2>Your department access</h2>

        <div class="departments">
            @forelse ($memberships as $membership)
                <div class="department">
                    <p>
                        <strong>{{ $membership->name }}</strong><br>
                        Role: {{ ucfirst($membership->role) }}
                    </p>
                </div>
            @empty
                <p>No department has been assigned to your account.</p>
            @endforelse
        </div>
    </section>
</main>
</body>
</html>