@extends('auth.security-layout')

@section('title', 'Confirm password')

@section('content')
    <h1>Confirm your password</h1>

    <p>
        Enter your current account password to access your security settings.
    </p>

    <form method="POST" action="{{ url('/user/confirm-password') }}">
        @csrf

        <label for="password">Current password</label>
        <input
            id="password"
            name="password"
            type="password"
            autocomplete="current-password"
            required
            autofocus
        >

        <button type="submit">Confirm password</button>
    </form>

    <p><a href="{{ route('dashboard') }}">Back to dashboard</a></p>
@endsection