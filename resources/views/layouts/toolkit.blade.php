<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="no-referrer">
    <title>@yield('title', 'NACSA Toolkit')</title>
    @include('partials.toolkit-styles')
@include('partials.toolkit-theme')
@include('partials.page-transitions')
<script>
try {
    const saved = localStorage.getItem('nacsa.sidebar.closed');
    document.documentElement.classList.toggle('nav-collapsed', saved === null ? matchMedia('(max-width: 760px)').matches : saved === 'true');
} catch (_) { document.documentElement.classList.toggle('nav-collapsed', matchMedia('(max-width: 760px)').matches); }
</script>
</head>
<body>
@include('partials.toolkit-icons')
<a class="skip-link" href="#main-content">Skip to main content</a>
@include('partials.toolkit-sidebar')
<button class="sidebar-backdrop" id="sidebar-backdrop" type="button" aria-label="Close navigation" tabindex="-1"></button>
<div class="workspace">
    <header class="topbar">
        <div class="topbar-heading">
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
@include('partials.sidebar-behaviour')
@yield('scripts')
</body>
</html>
