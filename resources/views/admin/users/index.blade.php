@extends('layouts.toolkit')

@section('title')
User management | NACSA Toolkit
@endsection
@section('page-label', 'User management')
@section('page-class', '')

@section('content')
@php
    $signedInUser = auth()->user();
@endphp
        <div class="page-heading"><div>
            <p class="eyebrow">Your risk workspace</p>
            <h1>User management</h1>
            <p>Create accounts and manage department access. System Admin permission is separate from department roles.</p>
        </div></div>
    @if (session('success'))
        <div class="success" role="status">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="errors" role="alert"><ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul></div>
    @endif

    <section class="settings-panel">
        <h2>Create an account</h2>
        <p>The password must have at least 12 characters, uppercase and lowercase letters, a number, and a symbol. Share it privately with the intended user.</p>
        @if ($departments->isEmpty())
            <p>Create a department before creating an account here.</p>
        @else
            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                <div class="grid">
                    <label>Name<input name="name" value="{{ old('name') }}" maxlength="255" required></label>
                    <label>Email<input name="email" type="email" value="{{ old('email') }}" maxlength="255" required></label>
                    <label>Password<input name="password" type="password" autocomplete="new-password" minlength="12" maxlength="72" required></label>
                    <label>Confirm password<input name="password_confirmation" type="password" autocomplete="new-password" minlength="12" maxlength="72" required></label>
                    <label>Department
                        <select name="department_id" required>
                            <option value="">Select a department</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}" @selected(old('department_id') == $department->id)>{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>Department role
                        <select name="role" required>
                            <option value="">Select a role</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role }}" @selected(old('role') === $role)>{{ ucfirst($role) }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
                <button class="submit" type="submit">Create account</button>
            </form>
        @endif
    </section>

    <section class="settings-panel">
        <h2>Accounts</h2>
        @forelse ($users as $managedUser)
            <article class="managed-account">
                <h3>{{ $managedUser->name }}</h3>
                <p>{{ $managedUser->email }}<br>
                    System Admin: <strong>{{ $managedUser->is_system_admin ? 'Yes' : 'No' }}</strong><br>
                    MFA: {{ filled($managedUser->two_factor_secret) && $managedUser->two_factor_confirmed_at !== null ? 'Enrolled' : 'Enrollment required' }}
                </p>
                <h4>Department access</h4>
                @forelse ($memberships->get($managedUser->id, collect()) as $membership)
                    <div class="row">
                        <span>{{ $membership->department_name }} — {{ ucfirst($membership->role) }}</span>
                        <form method="POST" action="{{ route('admin.users.remove', ['user' => $managedUser->id, 'department' => $membership->department_id]) }}" onsubmit="return confirm('Remove this user’s access to this department?');">
                            @csrf
                            @method('DELETE')
                            <button class="remove" type="submit" aria-label="Remove {{ $managedUser->name }} from {{ $membership->department_name }}">Remove access</button>
                        </form>
                    </div>
                @empty
                    <p>No department access assigned.</p>
                @endforelse
                @if ($departments->isNotEmpty())
                    <details>
                        <summary class="submit">Add or change a department role</summary>
                        <p>Selecting an existing department replaces that department’s role.</p>
                        <form method="POST" action="{{ route('admin.users.assign', ['user' => $managedUser->id]) }}">
                            @csrf
                            @method('PUT')
                            <div class="grid">
                                <label>Department
                                    <select name="department_id" required>
                                        <option value="">Select a department</option>
                                        @foreach ($departments as $department)
                                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label>Department role
                                    <select name="role" required>
                                        <option value="">Select a role</option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role }}">{{ ucfirst($role) }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            </div>
                            <button class="submit" type="submit">Save department role</button>
                        </form>
                    </details>
                @endif
            </article>
        @empty
            <p>No accounts found.</p>
        @endforelse
        <nav class="pagination" aria-label="Account pages">
            @if ($users->previousPageUrl())<a href="{{ $users->previousPageUrl() }}">Previous</a>@endif
            <span>Page {{ $users->currentPage() }} of {{ $users->lastPage() }}</span>
            @if ($users->nextPageUrl())<a href="{{ $users->nextPageUrl() }}">Next</a>@endif
        </nav>
    </section>
        
    
@endsection
