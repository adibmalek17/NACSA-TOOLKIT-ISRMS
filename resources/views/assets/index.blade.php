@extends('layouts.toolkit')

@section('title')
Asset Register | NACSA Toolkit
@endsection
@section('page-label', 'Asset register')
@section('page-class', 'asset-list-page')

@section('content')
@php
    $user = auth()->user();
@endphp
        <div class="page-heading">
            <div>
                
                <h1>Asset register</h1>
                
            </div>
            <a class="button" href="{{ route('assessments.index') }}">Open assessments <svg aria-hidden="true"><use href="#icon-arrow"/></svg></a>
        </div>
    <section class="asset-panel" aria-labelledby="add-asset-heading">
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
            <h2 id="add-asset-heading">Add an asset</h2>
            

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
                        Serial number (optional)
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
                        Description (optional)
                        <textarea
                            name="description"
                            maxlength="2000"
                        >{{ old('description') }}</textarea>
                    </label>
                </p>

                <button class="button save-asset" type="submit">Save asset <svg aria-hidden="true"><use href="#icon-arrow"/></svg></button>
            </form>
        @else
            <p>
                You do not have permission to add assets.
                An Admin or Assessor department assignment is required.
            </p>
        @endif
    </section>

    <section class="asset-panel register-panel" aria-labelledby="registered-assets-heading">
        <div class="register-heading"><div><h2 id="registered-assets-heading">Registered assets</h2><p class="section-intro">Assets in your assigned departments.</p></div><span class="register-count">{{ number_format($assets->total()) }} assets</span></div>

        <div class="table-wrap" tabindex="0" role="region" aria-label="Registered assets table">
            <table>
                <thead>
                    <tr>
                        <th scope="col">Code</th>
                        <th scope="col">Name</th>
                        <th scope="col">Department</th>
                        <th scope="col">Category</th>
                        <th scope="col">Owner</th>
                        <th scope="col">Location</th>
                        <th scope="col">Quantity</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($assets as $asset)
                        <tr>
                            <td><span class="asset-code">{{ $asset->code }}</span></td>
                            <td>{{ $asset->name }}</td>
                            <td>{{ $asset->department_name }}</td>
                            <td><span class="category-badge">{{ $asset->category }}</span></td>
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
                                        <span class="read-only">View only</span>
                                    @endif

                                    @if ($adminDepartments->contains('id', $asset->department_id))
                                        <form
                                            method="POST"
                                            action="{{ route('assets.destroy', $asset->id) }}"
                                           onsubmit="return confirm('Permanently delete this asset? This cannot be undone.');"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="danger"
                                            >Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty-assets"><strong>No assets registered yet.</strong><span>Registered assets will appear here.</span></td>
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
        
    
@endsection
