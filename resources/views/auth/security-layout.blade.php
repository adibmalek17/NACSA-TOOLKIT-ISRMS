<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="no-referrer">
    <title>@yield('title', 'Account security') | NACSA Toolkit</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            font-family: "Segoe UI", sans-serif;
            background: #14263d;
            color: #1e293b;
        }

        main {
            width: 100%;
            max-width: 440px;
            padding: clamp(20px, 5vw, 36px);
            background: white;
            border: 1px solid #dce3ec;
            border-radius: 12px;
        }

        .brand {
            color: #2457c5;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 2px;
        }

        .brand small { display: inline; color: #526176; font-size: 10px; }

        h1 {
            margin-bottom: 8px;
        }

        p {
            color: #526176;
            line-height: 1.6;
        }

        label {
            display: block;
            margin-top: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        input {
            width: 100%;
            margin-top: 8px;
            padding: 12px;
            border: 1px solid #bcc9d8;
            border-radius: 7px;
            font: inherit;
        }

        button {
            width: 100%;
            margin-top: 26px;
            padding: 13px;
            border: none;
            border-radius: 7px;
            background: #2457c5;
            color: white;
            font: inherit;
            font-weight: 600;
            cursor: pointer;
        }

        button:hover {
            background: #1d46a0;
        }

        input:focus-visible,
        button:focus-visible {
            outline: 3px solid #6996ef;
            outline-offset: 3px;
        }

        .errors {
            padding: 12px;
            background: #fff0ee;
            color: #a13024;
            border-radius: 7px;
        }

        .errors p {
            margin: 4px 0;
            color: inherit;
        }

        .note {
            margin-top: 24px;
            font-size: 12px;
        }

        a { color:#2457c5; text-underline-offset:3px; }
        a:focus-visible, summary:focus-visible { outline:3px solid #6996ef; outline-offset:3px; }
        details { margin-top:24px; padding:16px; border:1px solid #dce3ec; border-radius:7px; background:#f4f6f9; }
        summary { cursor:pointer; font-size:14px; font-weight:600; line-height:1.5; }
        details p { font-size:14px; }
        .errors ul { margin:0; padding-left:20px; }
        #code { text-align:center; font-size:24px; font-weight:600; letter-spacing:6px; font-variant-numeric:tabular-nums; }
        .back-link { margin:20px 0 0; font-size:14px; }
    </style>
    @include('partials.page-transitions')
</head>
<body>
<main id="main-content">
    <div class="brand">NACSA TOOLKIT <small>ISRMS</small></div>

    @if ($errors->any())
        <div class="errors" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</main>
</body>
</html>