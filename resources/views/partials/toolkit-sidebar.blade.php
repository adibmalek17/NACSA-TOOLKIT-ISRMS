<aside class="sidebar" id="toolkit-sidebar" aria-label="Sidebar">
    <a class="brand" href="{{ route('dashboard') }}" aria-label="NACSA Toolkit home">
        <span class="brand-mark"><svg aria-hidden="true"><use href="#icon-shield"/></svg></span>
        <span><strong>NACSA</strong><small>Risk toolkit</small></span>
    </a>
    <nav class="nav-links sidebar-navigation" aria-label="Main navigation">
        @foreach ([
            ['dashboard', 'Dashboard', 'dashboard', request()->routeIs('dashboard')],
            ['assets.index', 'Asset register', 'assets', request()->routeIs('assets.*')],
            ['assessments.index', 'Assessments', 'document', request()->routeIs('assessments.*')],
            ['account.security', 'Account security', 'lock', request()->routeIs('account.security')],
        ] as [$navRoute, $navLabel, $navIcon, $navActive])
            <a class="nav-link {{ $navActive ? 'active' : '' }}" href="{{ route($navRoute) }}" @if ($navActive) aria-current="page" @endif>
                <svg aria-hidden="true"><use href="#icon-{{ $navIcon }}"/></svg>{{ $navLabel }}
            </a>
        @endforeach
        @if (auth()->user()->is_system_admin)
            <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}" @if (request()->routeIs('admin.users.*')) aria-current="page" @endif>
                <svg aria-hidden="true"><use href="#icon-users"/></svg>User management
            </a>
        @endif
    </nav>
</aside>
