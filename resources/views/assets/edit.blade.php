<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Asset | NACSA Toolkit</title>

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
            max-width: 900px;
            margin: 24px auto;
        }

        section {
            padding: 28px;
            background: white;
            border-radius: 12px;
        }

        a { color: #087c72; }

        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        label {
            display: block;
            font-weight: 600;
        }

        input, select, textarea {
            display: block;
            width: 100%;
            margin-top: 8px;
            padding: 11px;
            border: 1px solid #cbd8dd;
            border-radius: 6px;
            font: inherit;
        }

        textarea { min-height: 100px; }

        button {
            padding: 12px 24px;
            border: 0;
            border-radius: 7px;
            background: #087c72;
            color: white;
            font: inherit;
            cursor: pointer;
        }

        button:hover { background: #06675f; }

        .errors {
            padding: 15px;
            margin-bottom: 20px;
            background: #fff0ee;
            color: #a13024;
            border-radius: 7px;
        }

        .actions {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-top: 24px;
        }

        input:focus-visible,
        select:focus-visible,
        textarea:focus-visible,
        button:focus-visible,
        a:focus-visible {
            outline: 3px solid #55baa9;
            outline-offset: 3px;
        }

        @media (max-width: 650px) {
            body { padding: 12px; }
            section { padding: 18px; }
            .grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<main>
    <p>
        <a href="{{ route('assets.index') }}">← Asset register</a>
    </p>

    <section>
        <h1>Edit asset</h1>

        <p>
            Department: <strong>{{ $departmentName }}</strong>
        </p>

        @if ($errors->any())
            <div class="errors" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST"
              action="{{ route('assets.update', $asset) }}">
            @csrf
            @method('PUT')

            <div class="grid">
                <label>
                    Asset code
                    <input
                        name="code"
                        value="{{ old('code', $asset->code) }}"
                        maxlength="80"
                        required
                    >
                </label>

                <label>
                    Asset name
                    <input
                        name="name"
                        value="{{ old('name', $asset->name) }}"
                        maxlength="180"
                        required
                    >
                </label>

                <label>
                    Category
                    <select name="category" required>
                        @foreach ($categories as $category)
                            <option
                                value="{{ $category }}"
                                @selected(
                                    old('category', $asset->category) === $category
                                )
                            >
                                {{ $category }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <label>
                    Serial number — optional
                    <input
                        name="serial_number"
                        value="{{ old('serial_number', $asset->serial_number) }}"
                        maxlength="180"
                    >
                </label>

                <label>
                    Quantity
                    <input
                        name="quantity"
                        type="number"
                        value="{{ old('quantity', $asset->quantity) }}"
                        min="1"
                        max="1000000"
                        step="1"
                        required
                    >
                </label>

                <label>
                    Asset owner
                    <input
                        name="owner"
                        value="{{ old('owner', $asset->owner) }}"
                        maxlength="180"
                        required
                    >
                </label>

                <label>
                    Location
                    <input
                        name="location"
                        value="{{ old('location', $asset->location) }}"
                        maxlength="180"
                        required
                    >
                </label>
            </div>

            <p>
                <label>
                    Description — optional
                    <textarea
                        name="description"
                        maxlength="2000"
                    >{{ old('description', $asset->description) }}</textarea>
                </label>
            </p>

            <div class="actions">
                <button type="submit">Save changes</button>
                <a href="{{ route('assets.index') }}">Cancel</a>
            </div>
        </form>
    </section>
</main>
</body>
</html>