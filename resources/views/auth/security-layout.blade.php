<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="no-referrer">
    <title>@yield('title', 'Account security') | NACSA Toolkit</title>

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
            max-width: 720px;
            margin: 32px auto;
            padding: 28px;
            background: white;
            border-radius: 12px;
        }

        p { line-height: 1.7; }
        a { color: #087c72; }
        label { display: block; font-weight: 600; }

        input {
            display: block;
            width: 100%;
            margin: 8px 0 18px;
            padding: 12px;
            border: 1px solid #cbd8dd;
            border-radius: 6px;
            font: inherit;
        }

        button {
            padding: 12px 22px;
            border: 0;
            border-radius: 7px;
            background: #087c72;
            color: white;
            font: inherit;
            cursor: pointer;
        }

        button:hover { background: #06675f; }
        .danger { background: #a13024; }

        .errors, .notice {
            padding: 16px;
            margin: 18px 0;
            border-radius: 7px;
        }

        .errors { background: #fff0ee; color: #a13024; }
        .notice { background: #e5f5ee; color: #1e7251; }

        .qr {
            margin: 20px 0;
            padding: 16px;
            background: white;
        }

        .qr svg { max-width: 100%; height: auto; }

        .codes {
            padding: 16px;
            background: #f4f6f8;
            line-height: 1.8;
            overflow-wrap: anywhere;
        }

        section + section {
            margin-top: 28px;
            padding-top: 20px;
            border-top: 1px solid #e0e7eb;
        }

        summary { cursor: pointer; margin: 20px 0; }

        input:focus-visible,
        button:focus-visible,
        a:focus-visible,
        summary:focus-visible {
            outline: 3px solid #55baa9;
            outline-offset: 3px;
        }

        @media (max-width: 600px) {
            body { padding: 12px; }
            main { margin: 12px auto; padding: 20px; }
        }
    </style>
</head>
<body>
<main>
    <p><strong>NACSA TOOLKIT</strong></p>

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