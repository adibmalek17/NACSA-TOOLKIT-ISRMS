<aside class="sidebar" id="toolkit-sidebar" aria-label="Sidebar">
    <div class="sidebar-header">
    <a class="brand" href="{{ route('dashboard') }}" aria-label="NACSA Toolkit ISRMS home">
        <span class="brand-mark"><svg aria-hidden="true"><use href="#icon-shield"/></svg></span>
        <span><strong>NACSA TOOLKIT</strong><small>ISRMS</small></span>
    </a>
    <button type="button" id="sidebar-toggle" class="sidebar-toggle" aria-controls="sidebar-navigation" aria-expanded="true" aria-label="Collapse sidebar" title="Collapse sidebar (Ctrl+Shift+S)">
        <svg aria-hidden="true" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="3"/><path d="M9 4v16"/></svg>
    </button>
    </div>
    <nav id="sidebar-navigation" class="nav-links sidebar-navigation" aria-label="Main navigation">
        @foreach ([
            ['dashboard', 'Dashboard', 'dashboard', request()->routeIs('dashboard')],
            ['assets.index', 'Asset register', 'assets', request()->routeIs('assets.*')],
            ['assessments.index', 'Assessments', 'document', request()->routeIs('assessments.*')],
            ['account.security', 'Account security', 'lock', request()->routeIs('account.security')],
        ] as [$navRoute, $navLabel, $navIcon, $navActive])
            <a class="nav-link {{ $navActive ? 'active' : '' }}" href="{{ route($navRoute) }}" aria-label="{{ $navLabel }}" title="{{ $navLabel }}" @if ($navActive) aria-current="page" @endif>
                <svg aria-hidden="true"><use href="#icon-{{ $navIcon }}"/></svg><span class="nav-link-label">{{ $navLabel }}</span>
            </a>
        @endforeach
        @if (auth()->user()->is_system_admin)
            <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}" aria-label="User management" title="User management" @if (request()->routeIs('admin.users.*')) aria-current="page" @endif>
                <svg aria-hidden="true"><use href="#icon-users"/></svg><span class="nav-link-label">User management</span>
            </a>
        @endif
    </nav>
</aside>
