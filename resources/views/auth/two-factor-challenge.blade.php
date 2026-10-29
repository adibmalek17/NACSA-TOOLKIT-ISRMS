@extends('auth.security-layout')

@section('title', 'Verify sign in')

@section('content')
    <h1>Verify your sign in</h1>

    <p>Enter the six-digit code from your authenticator app.</p>

    <form method="POST" action="{{ url('/two-factor-challenge') }}">
        @csrf

        <label for="code">Authentication code</label>
        <input
            id="code"
            name="code"
            type="text"
            inputmode="numeric"
            autocomplete="one-time-code"
            pattern="[0-9]{6}"
            minlength="6"
            maxlength="6"
            required
            autofocus
        >

        <button type="submit">Verify and sign in</button>
    </form>

    <details>
        <summary>Use a recovery code instead</summary>

        <p>
            If you cannot access your authenticator app, enter one of
            your saved recovery codes. Each code can be used once.
        </p>

        <form method="POST" action="{{ url('/two-factor-challenge') }}">
            @csrf

            <label for="recovery_code">Recovery code</label>
            <input
                id="recovery_code"
                name="recovery_code"
                type="text"
                autocomplete="off"
                spellcheck="false"
                required
            >

            <button type="submit">Sign in with recovery code</button>
        </form>
    </details>

    <p><a href="{{ route('login') }}">Back to sign in</a></p>
@endsection