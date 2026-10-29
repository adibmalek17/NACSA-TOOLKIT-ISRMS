<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Asset Register | NACSA Toolkit</title>

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

        textarea { min-height: 80px; }

        button {
            margin-top: 20px;
            padding: 12px 24px;
            border: 0;
            border-radius: 7px;
            background: #087c72;
            color: white;
            font: inherit;
            cursor: pointer;
        }

        button:hover { background: #06675f; }

        input:focus-visible,
        select:focus-visible,
        textarea:focus-visible,
        button:focus-visible,
        a:focus-visible {
            outline: 3px solid #55baa9;
            outline-offset: 2px;
        }

        .success, .errors {
            padding: 15px;
            border-radius: 7px;
            margin-bottom: 20px;
        }

        .success {
            background: #e5f5ee;
            color: #1e7251;
        }

        .errors {
            background: #fff0ee;
            color: #a13024;
        }

        .table-wrap { overflow-x: auto; }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th, td {
            padding: 13px;
            border-bottom: 1px solid #e0e7eb;
        }

        th { background: #f4f6f8; }

        .asset-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            white-space: nowrap;
        }

        .asset-actions form { margin: 0; }

        .archive-button {
            margin: 0;
            padding: 8px 12px;
            background: #a13024;
            font-size: 14px;
        }

        .archive-button:hover { background: #81261d; }

        .pagination {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 20px;
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
        <a href="{{ route('dashboard') }}">← Dashboard</a>
    </p>

    <h1>Asset register</h1>
    <p>Register and view assets in your assigned departments.</p>

    <section>
        @if (session('success'))
            <div class="success" role="status">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="errors" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($writableDepartments->isNotEmpty())
            <h2>Add an asset</h2>

            <form method="POST" action="{{ route('assets.store') }}">
                @csrf

                <div class="grid">
                    <label>
                        Department
                        <select name="department_id" required>
                            @foreach ($writableDepartments as $department)
                                <option
                                    value="{{ $department->id }}"
                                    @selected(
                                        old('department_id') == $department->id
                                    )
                                >
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label>
                        Category
                        <select name="category" required>
                            @foreach ($categories as $category)
                                <option
                                    value="{{ $category }}"
                                    @selected(old('category') === $category)
                                >
                                    {{ $category }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label>
                        Asset code
                        <input
                            name="code"
                            value="{{ old('code') }}"
                            maxlength="80"
                            required
                        >
                    </label>

                    <label>
                        Asset name
                        <input
                            name="name"
                            value="{{ old('name') }}"
                            maxlength="180"
                            required
                        >
                    </label>

                    <label>
                        Serial number — optional
                        <input
                            name="serial_number"
                            value="{{ old('serial_number') }}"
                            maxlength="180"
                        >
                    </label>

                    <label>
                        Quantity
                        <input
                            name="quantity"
                            type="number"
                            value="{{ old('quantity', 1) }}"
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
                            value="{{ old('owner') }}"
                            maxlength="180"
                            required
                        >
                    </label>

                    <label>
                        Location
                        <input
                            name="location"
                            value="{{ old('location') }}"
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
                        >{{ old('description') }}</textarea>
                    </label>
                </p>

                <button type="submit">Save asset</button>
            </form>
        @else
            <p>
                You do not have permission to add assets.
                An Admin or Assessor department assignment is required.
            </p>
        @endif
    </section>

    <section>
        <h2>Registered assets</h2>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Category</th>
                        <th>Owner</th>
                        <th>Location</th>
                        <th>Quantity</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($assets as $asset)
                        <tr>
                            <td>{{ $asset->code }}</td>
                            <td>{{ $asset->name }}</td>
                            <td>{{ $asset->department_name }}</td>
                            <td>{{ $asset->category }}</td>
                            <td>{{ $asset->owner }}</td>
                            <td>{{ $asset->location }}</td>
                            <td>{{ $asset->quantity }}</td>

                            <td>
                                <div class="asset-actions">
                                    @if ($writableDepartments->contains('id', $asset->department_id))
                                        <a href="{{ route('assets.edit', $asset->id) }}">
                                            Edit
                                        </a>
                                    @else
                                        <span>View only</span>
                                    @endif

                                    @if ($adminDepartments->contains('id', $asset->department_id))
                                        <form
                                            method="POST"
                                            action="{{ route('assets.destroy', $asset->id) }}"
                                            onsubmit="return confirm('Archive this asset? It will be hidden from the active register.');"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="archive-button"
                                            >
                                                Archive
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">No assets registered yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">
            @if ($assets->previousPageUrl())
                <a href="{{ $assets->previousPageUrl() }}">Previous</a>
            @endif

            <span>
                Page {{ $assets->currentPage() }} of {{ $assets->lastPage() }}
            </span>

            @if ($assets->nextPageUrl())
                <a href="{{ $assets->nextPageUrl() }}">Next</a>
            @endif
        </div>
    </section>
</main>
</body>
</html>