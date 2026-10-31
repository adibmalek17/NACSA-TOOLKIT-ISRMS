@extends('layouts.toolkit')

@section('title')
Edit Asset | NACSA Toolkit
@endsection
@section('page-label', 'Asset register')
@section('page-class', '')

@section('content')

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

@endsection
