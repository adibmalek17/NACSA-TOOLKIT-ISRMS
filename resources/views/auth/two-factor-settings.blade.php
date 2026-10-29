@extends('auth.security-layout')

@section('title', 'Two-factor authentication')

@section('content')
    <p><a href="{{ route('dashboard') }}">← Dashboard</a></p>

    <h1>Two-factor authentication</h1>

    <p>Account: {{ $user->email }}</p>

    @if (! $user->two_factor_secret || ! $user->two_factor_confirmed_at)
    <div class="notice" role="status">
        Two-factor authentication is required.
        Complete setup below before accessing the toolkit.
    </div>
@endif

<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit">Sign out</button>
</form>

    @if (session('status') === 'two-factor-authentication-confirmed')
        <div class="notice" role="status">
            Two-factor authentication is now enabled.
            Save your recovery codes below.
        </div>
    @elseif (session('status') === 'two-factor-authentication-disabled')
        <div class="notice" role="status">
            Two-factor authentication has been disabled.
        </div>
    @elseif (session('status') === 'recovery-codes-generated')
        <div class="notice" role="status">
            New recovery codes have been generated.
            Replace your previously saved codes with these.
        </div>
    @endif

    @if (! $user->two_factor_secret)
        <section>
            <h2>Status: Not enabled</h2>

            <p>
                Protect your account with your password and a code
                from an authenticator app.
            </p>

            <form method="POST"
                  action="{{ url('/user/two-factor-authentication') }}">
                @csrf
                <button type="submit">Set up two-factor authentication</button>
            </form>
        </section>
    @elseif (! $user->two_factor_confirmed_at)
        <section>
            <h2>Finish setup</h2>

            <p>
                Scan this QR code using your authenticator app.
                Then enter the six-digit code shown in the app.
            </p>

            <div class="qr">
                {!! $user->twoFactorQrCodeSvg() !!}
            </div>

            <p>
                Two-factor authentication is not active until you
                successfully confirm a code.
            </p>

            <form method="POST"
                  action="{{ url('/user/confirmed-two-factor-authentication') }}">
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
                >

                <button type="submit">Confirm and enable</button>
            </form>
        </section>

        <section>
            <form method="POST"
                  action="{{ url('/user/two-factor-authentication') }}">
                @csrf
                @method('DELETE')

                <button type="submit" class="danger">Cancel setup</button>
            </form>
        </section>
    @else
        <section>
            <h2>Status: Enabled</h2>

            <p>
                When you next sign in with your password, you will also
                be asked for an authentication code.
            </p>

            <h3>Recovery codes</h3>

            <p>
                Save these somewhere private before signing out.
                They allow you to sign in if you lose access to your
                authenticator. Each code can be used once.
            </p>

            <div class="codes">
                @foreach ($user->recoveryCodes() as $recoveryCode)
                    <div><code>{{ $recoveryCode }}</code></div>
                @endforeach
            </div>

            <p>Generating new codes invalidates your previous recovery codes.</p>

            <form method="POST"
                  action="{{ url('/user/two-factor-recovery-codes') }}">
                @csrf
                <button type="submit">Generate new recovery codes</button>
            </form>
        </section>

        <section>
            <h2>Disable two-factor authentication</h2>

            <p>This removes the authentication-code requirement from your account.</p>

            <form method="POST"
                  action="{{ url('/user/two-factor-authentication') }}">
                @csrf
                @method('DELETE')

                <button type="submit" class="danger">
                    Disable two-factor authentication
                </button>
            </form>
        </section>
    @endif
@endsection