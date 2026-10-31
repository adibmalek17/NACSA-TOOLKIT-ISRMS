<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="no-referrer">
    <title>@yield('title', 'NACSA Toolkit')</title>
    @include('partials.toolkit-styles')
<style>
        .topbar .sidebar-toggle {
            width: 36px;
            height: 36px;
            min-height: 36px;
            padding: 7px;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: #52616b;
        }
        .topbar .sidebar-toggle:hover { background: #edf0f2; color: #193540; }
        .topbar .sidebar-toggle svg { width: 22px; height: 22px; stroke-width: 1.7; }
    </style>
</head>
<body>
@include('partials.toolkit-icons')
<a class="skip-link" href="#main-content">Skip to main content</a>
@include('partials.toolkit-sidebar')
<div class="workspace">
    <header class="topbar">
        <div class="topbar-heading">
            <button type="button" id="sidebar-toggle" class="sidebar-toggle" aria-controls="toolkit-sidebar" aria-expanded="true" aria-label="Hide navigation">
                <svg aria-hidden="true" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="3"/><path d="M9 4v16"/></svg>
            </button>
            <strong class="page-label">@yield('page-label')</strong>
        </div>
        <div class="topbar-actions">
            <div class="account">
                <span class="avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                <div><span class="account-name">{{ auth()->user()->name }}</span><span class="account-email">{{ auth()->user()->email }}</span></div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="topbar-signout-form">
                @csrf
                <button class="topbar-signout" type="submit"><svg aria-hidden="true"><use href="#icon-signout"/></svg>Sign out</button>
            </form>
        </div>
    </header>
    <main id="main-content" class="toolkit-content @yield('page-class')">
        @yield('content')
        <footer class="page-footer"><span>NACSA Toolkit · Risk assessment workspace</span></footer>
    </main>
</div>
<script>
(() => {
    const button = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('toolkit-sidebar');
    const smallScreen = window.matchMedia('(max-width: 760px)');
    let stored = null;
    try { stored = localStorage.getItem('nacsa.sidebar.closed'); } catch (_) {}
    function setClosed(closed, remember = false) {
        document.body.classList.toggle('sidebar-closed', closed);
        sidebar.inert = closed;
        sidebar.setAttribute('aria-hidden', String(closed));
        button.setAttribute('aria-expanded', String(!closed));
        button.setAttribute('aria-label', closed ? 'Open navigation' : 'Hide navigation');
        if (remember) { try { localStorage.setItem('nacsa.sidebar.closed', String(closed)); } catch (_) {} }
    }
    setClosed(stored === null ? smallScreen.matches : stored === 'true');
    button.addEventListener('click', () => setClosed(!document.body.classList.contains('sidebar-closed'), true));
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && !document.body.classList.contains('sidebar-closed')) {
            setClosed(true, true);
            button.focus();
        }
    });
})();
</script>
@yield('scripts')
</body>
</html>
