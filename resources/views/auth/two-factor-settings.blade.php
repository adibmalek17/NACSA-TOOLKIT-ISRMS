@extends('layouts.toolkit')

@section('title')
Account security | NACSA Toolkit
@endsection
@section('page-label', 'Account security')
@section('page-class', '')

@section('content')
@php
    $signedInUser = auth()->user();
@endphp
        <div class="page-heading"><div>
            <p class="eyebrow">Your risk workspace</p>
            <h1>Account security</h1>
            <p>Manage two-factor authentication and recovery codes for your account.</p>
        </div></div>
    @if (! config('fortify.mfa_enabled', true))
        <section class="settings-panel">
            <div class="section-heading">
                <h2>Two-factor authentication</h2>
                <span class="state-badge pending">Temporarily disabled</span>
            </div>
            <p>Two-factor authentication is turned off for local development.</p>
            <p>Setup, recovery codes and MFA management are unavailable while it is disabled. Your existing enrollment is retained.</p>
        </section>
    @else
    @if (! $user->two_factor_secret || ! $user->two_factor_confirmed_at)
        <div class="notice" role="status">
            Two-factor authentication is required.
            Complete setup below before accessing the toolkit.
        </div>
    @endif

    @if (session('status') === 'two-factor-authentication-confirmed')
        <div class="success" role="status">
            Two-factor authentication is now enabled. Save your recovery codes below.
        </div>
    @elseif (session('status') === 'two-factor-authentication-disabled')
        <div class="notice" role="status">Two-factor authentication has been disabled.</div>
    @elseif (session('status') === 'recovery-codes-generated')
        <div class="success" role="status">
            New recovery codes have been generated. Replace your previously saved codes with these.
        </div>
    @endif

    @foreach ($errors->getBags() as $errorBag)
        @if ($errorBag->any())
            <div class="errors" role="alert">
                <ul>
                    @foreach ($errorBag->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    @endforeach

    @if (! $user->two_factor_secret)
        <section class="settings-panel">
            <div class="section-heading"><h2>Two-factor authentication</h2><span class="state-badge pending">Not enabled</span></div>
            <p>Protect your account with your password and a code from an authenticator app.</p>
            <form method="POST" action="{{ url('/user/two-factor-authentication') }}">
                @csrf
                <button type="submit">Set up two-factor authentication</button>
            </form>
        </section>
    @elseif (! $user->two_factor_confirmed_at)
        <section class="settings-panel">
            <div class="section-heading"><h2>Finish setup</h2><span class="state-badge pending">Confirmation needed</span></div>
            <p>Scan this QR code using your authenticator app. Then enter the six-digit code shown in the app.</p>
            <div class="qr">{!! $user->twoFactorQrCodeSvg() !!}</div>
            <p>Two-factor authentication is not active until you successfully confirm a code.</p>
            <form method="POST" action="{{ url('/user/confirmed-two-factor-authentication') }}" class="confirmation-form">
                @csrf
                <label for="code">Authentication code</label>
                <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" minlength="6" maxlength="6" required>
                <button type="submit" class="submit">Confirm and enable</button>
            </form>
        </section>
        <section class="settings-panel">
            <h2>Cancel setup</h2>
            <form method="POST" action="{{ url('/user/two-factor-authentication') }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="danger">Cancel setup</button>
            </form>
        </section>
    @else
        <section class="settings-panel">
            <div class="section-heading"><h2>Two-factor authentication</h2><span class="state-badge">Enabled</span></div>
            <p>When you next sign in with your password, you will also be asked for an authentication code.</p>
            <h3>Recovery codes</h3>
            <p>Save these somewhere private before signing out. They allow you to sign in if you lose access to your authenticator. Each code can be used once.</p>
            <div class="codes">
                @foreach ($user->recoveryCodes() as $recoveryCode)
                    <div><code>{{ $recoveryCode }}</code></div>
                @endforeach
            </div>
            <p>Generating new codes invalidates your previous recovery codes.</p>
            <form method="POST" action="{{ url('/user/two-factor-recovery-codes') }}">
                @csrf
                <button type="submit">Generate new recovery codes</button>
            </form>
        </section>
        <section class="settings-panel danger-panel">
            <h2>Disable two-factor authentication</h2>
            <p>This removes the authentication-code requirement from your account.</p>
            <form method="POST" action="{{ url('/user/two-factor-authentication') }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="danger">Disable two-factor authentication</button>
            </form>
        </section>
    @endif
    @endif
        
    
@endsection
