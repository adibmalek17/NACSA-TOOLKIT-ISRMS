<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in | NACSA Toolkit</title>
    <style>
        * { box-sizing: border-box; }
        body { margin:0; background:#f3f6f8; color:#193540; font:15px/1.6 "Segoe UI",Arial,sans-serif; }
        .login-shell { min-height:100vh; display:grid; grid-template-columns:minmax(320px, .9fr) minmax(440px, 1.1fr); }
        .identity { background:#102d39; color:#fff; padding:52px clamp(32px,5vw,88px); display:flex; flex-direction:column; justify-content:space-between; gap:64px; }
        .brand { display:flex; gap:14px; align-items:center; }
        .brand-icon { width:46px; height:46px; display:grid; place-items:center; border:1px solid #527c85; border-radius:12px; color:#80d5c6; }
        svg { width:24px; height:24px; fill:none; stroke:currentColor; stroke-width:1.7; stroke-linecap:round; stroke-linejoin:round; }
        .brand strong { display:block; font-size:21px; letter-spacing:1px; line-height:1.2; }
        .brand small { font-size:11px; letter-spacing:2.3px; color:#c2d4dc; text-transform:uppercase; }
        .intro { max-width:470px; }
        .eyebrow { color:#80d5c6; font-size:11px; font-weight:700; letter-spacing:2px; text-transform:uppercase; }
        .intro h2 { font-size:clamp(30px,3.2vw,46px); line-height:1.2; font-weight:600; letter-spacing:-1px; margin:20px 0; }
        .intro p { color:#c2d4dc; max-width:370px; }
        .steps { border-top:1px solid #36515c; margin-top:34px; padding-top:24px; display:flex; gap:24px; flex-wrap:wrap; color:#e3ecf0; font-size:13px; }
        .identity-footer { color:#b9cdd6; font-size:12px; }
        .form-side { display:flex; align-items:center; justify-content:center; padding:64px 36px; background:#fff; }
        .form-content { width:100%; max-width:400px; }
        .form-content h1 { font-size:32px; letter-spacing:-.7px; margin:0 0 8px; line-height:1.25; }
        .subtitle { color:#526974; margin:0 0 32px; }
        label { display:block; font-weight:600; font-size:13px; margin:22px 0 8px; }
        input { width:100%; min-height:48px; padding:12px 14px; border:1px solid #b8c9d1; border-radius:8px; color:#193540; background:#fff; font:inherit; }
        input:autofill { box-shadow:0 0 0 1000px #f4f8fb inset; }
        .password-field { position:relative; }
        .password-field input { padding-right:70px; }
        button { font:inherit; cursor:pointer; }
        .password-toggle { position:absolute; top:4px; right:5px; height:40px; padding:0 10px; border:0; border-radius:5px; background:transparent; color:#405c68; font-weight:600; font-size:12px; }
        .password-toggle:hover { background:#edf2f5; }
        .sign-in { display:flex; align-items:center; justify-content:center; gap:12px; width:100%; min-height:48px; margin-top:28px; border:1px solid #087f78; border-radius:8px; background:#087f78; color:white; font-size:14px; font-weight:600; }
        .sign-in:hover { background:#06665f; }
        .sign-in svg { width:18px; height:18px; }
        :focus-visible { outline:3px solid #39bdb0; outline-offset:3px; }
        .access-note { border-top:1px solid #e1e9ed; margin-top:32px; padding-top:20px; color:#526974; font-size:12px; }
        .errors,.status { margin:20px 0; padding:12px 16px; border:1px solid #edc5bf; border-radius:8px; color:#942f25; background:#fff1ef; font-size:13px; }
        .errors ul { margin:0; padding-left:18px; }
        .status { color:#21634f; background:#e8f6ef; border-color:#c5e2d4; }
        [hidden] { display:none !important; }
        @media(max-width:800px) {
            .login-shell { grid-template-columns:1fr; }
            .identity { padding:24px; gap:0; }
            .intro,.identity-footer { display:none; }
            .form-side { padding:48px 24px; align-items:flex-start; }
        }
        @media(prefers-reduced-motion:reduce) { * { scroll-behavior:auto; } }
    </style>
</head>
<body>
<main class="login-shell">
    <section class="identity" aria-label="NACSA Toolkit">
        <div class="brand">
            <span class="brand-icon"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 3 4 6v6c0 5 8 9 8 9s8-4 8-9V6z"/><path d="m8 12 3 3 5-6"/></svg></span>
            <div><strong>NACSA</strong><small>Risk toolkit</small></div>
        </div>
        <div class="intro">
            <span class="eyebrow">Information security</span>
            <h2>A clear view<br>of your organisation’s risk.</h2>
            <p>Manage assets, assess risks and track treatment plans in one workspace.</p>
            <div class="steps"><span>Asset register</span><span>Risk assessment</span><span>Treatment plans</span></div>
        </div>
        <div class="identity-footer">NACSA Toolkit · Risk assessment workspace</div>
    </section>
    <section class="form-side" aria-labelledby="signin-heading">
        <div class="form-content">
            <h1 id="signin-heading">Sign in</h1>
            <p class="subtitle">Enter your account details to access the toolkit.</p>
            @if ($errors->any())
                <div class="errors" role="alert"><ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul></div>
            @endif
            @if (session('status'))
                <p class="status" role="status">{{ session('status') }}</p>
            @endif
            <form method="POST" action="{{ route('login.store') }}">
                @csrf
                <label for="email">Email address</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
                <label for="password">Password</label>
                <div class="password-field">
                    <input id="password" name="password" type="password" autocomplete="current-password" required>
                    <button class="password-toggle" id="toggle-password" type="button" aria-controls="password" aria-label="Show password" aria-pressed="false" hidden>Show</button>
                </div>
                <button class="sign-in" type="submit">Sign in <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M5 12h14m-6-6 6 6-6 6"/></svg></button>
            </form>
            <p class="access-note">Access is for authorised users only.</p>
        </div>
    </section>
</main>
<script>
    const toggle = document.getElementById('toggle-password');
    const password = document.getElementById('password');
    toggle.hidden = false;
    toggle.addEventListener('click', () => {
        const visible = password.type === 'password';
        password.type = visible ? 'text' : 'password';
        toggle.textContent = visible ? 'Hide' : 'Show';
        toggle.setAttribute('aria-label', visible ? 'Hide password' : 'Show password');
        toggle.setAttribute('aria-pressed', String(visible));
    });
</script>
</body>
</html>
