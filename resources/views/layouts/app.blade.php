<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-canvas text-foreground">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php $pageTitle = trim($__env->yieldContent('title', __('common.brand.tagline'))); @endphp
    <title>{{ str_contains($pageTitle, 'PitchTicker') ? $pageTitle : $pageTitle.' · PitchTicker' }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <script>
        try { document.documentElement.dataset.theme = localStorage.getItem('pitchticker-theme') === 'dark' ? 'dark' : 'light'; }
        catch { document.documentElement.dataset.theme = 'light'; }
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fonts
</head>
<body class="public-body flex min-h-full flex-col bg-canvas text-foreground antialiased">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[100] focus:rounded-lg focus:bg-surface focus:p-3">{{ __('common.nav.skip') }}</a>
    @php
        $navItems = [
            ['home', ['home'], __('common.nav.home')],
            ['football.live', ['football.live'], __('common.nav.live')],
            ['football.matchday', ['football.matchday'], __('common.nav.schedule')],
            ['football.index', ['football.index', 'football.team', 'football.player', 'football.fixture'], __('common.nav.portal')],
            ['football.transfers', ['football.transfers'], __('common.nav.transfers')],
            ['news.index', ['news.*'], __('common.nav.news')],
        ];
    @endphp
    <header x-data="{ mobileOpen: false }" @keydown.escape.window="mobileOpen = false" class="sticky top-0 z-50 border-b border-divider bg-surface">
        <div class="mx-auto flex max-w-page flex-wrap items-center justify-between gap-2 px-4 py-3 sm:px-6">
            <a href="{{ route('home') }}" class="flex min-h-11 items-center gap-2 text-xl font-bold tracking-tight" aria-label="PitchTicker — {{ __('common.nav.home') }}">
                <svg viewBox="0 0 32 32" class="h-8 w-8" aria-hidden="true"><rect width="32" height="32" rx="6" fill="#14532D"/><path d="M9 8v16M9 8h9a5 5 0 0 1 0 10H9" fill="none" stroke="#FFFFFF" stroke-width="3"/><path d="M21 23h5" stroke="#E6B94A" stroke-width="3"/></svg>
                <span>PitchTicker</span>
            </a>
            <nav aria-label="{{ __('common.nav.menu') }}" class="hidden items-center gap-1 xl:flex">
                @foreach($navItems as [$route, $matches, $label])
                    <a href="{{ route($route) }}" @if(request()->routeIs(...$matches)) aria-current="page" @endif class="flex min-h-11 items-center border-b-2 px-3 text-sm font-semibold {{ request()->routeIs(...$matches) ? 'border-link text-link' : 'border-transparent text-muted hover:text-foreground' }}">{{ $label }}</a>
                @endforeach
            </nav>
            <div class="flex items-center gap-1 sm:gap-2">
                @auth <a href="{{ route('admin.dashboard') }}" aria-label="{{ __('common.nav.admin_panel') }}" class="flex h-11 w-11 items-center justify-center rounded-lg hover:bg-canvas"><x-icon name="user" class="h-5 w-5" /></a> @endauth
                <a href="{{ route('football.search') }}" aria-label="{{ __('common.nav.search_title') }}" class="flex h-11 w-11 items-center justify-center rounded-lg hover:bg-canvas"><svg viewBox="0 0 24 24" fill="none" class="h-5 w-5" aria-hidden="true"><circle cx="10" cy="10" r="6" stroke="currentColor" stroke-width="1.8"/><path d="m15 15 5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></a>
                <button type="button" data-theme-toggle aria-label="{{ __('common.nav.theme') }}" aria-pressed="false" class="flex h-11 w-11 items-center justify-center rounded-lg hover:bg-canvas"><svg viewBox="0 0 24 24" fill="none" class="h-5 w-5" aria-hidden="true"><circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.6"/><path d="M12 4a8 8 0 0 1 0 16z" fill="currentColor"/></svg></button>
                <a href="{{ route('locale.switch', app()->getLocale() === 'id' ? 'en' : 'id') }}" aria-label="{{ __('common.locale.label') }}: {{ app()->getLocale() === 'id' ? 'English' : 'Bahasa Indonesia' }}" class="hidden min-h-11 items-center px-2 text-sm font-semibold sm:flex">{{ strtoupper(app()->getLocale()) }}</a>
                <button type="button" @click="mobileOpen = !mobileOpen" :aria-expanded="mobileOpen" aria-controls="mobile-navigation" aria-label="{{ __('common.nav.menu') }}" class="flex h-11 w-11 items-center justify-center rounded-lg xl:hidden"><svg viewBox="0 0 24 24" fill="none" class="h-5 w-5" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></button>
            </div>
        </div>
        <nav id="mobile-navigation" x-show="mobileOpen" x-cloak aria-label="{{ __('common.nav.menu') }}" class="border-t border-divider px-4 py-2 xl:hidden">
            @foreach($navItems as [$route, $matches, $label])
                <a href="{{ route($route) }}" @if(request()->routeIs(...$matches)) aria-current="page" @endif class="flex min-h-11 items-center rounded-lg px-3 text-sm {{ request()->routeIs(...$matches) ? 'bg-canvas font-semibold text-link' : 'text-foreground' }}">{{ $label }}</a>
            @endforeach
            <a href="{{ route('locale.switch', app()->getLocale() === 'id' ? 'en' : 'id') }}" class="flex min-h-11 items-center px-3 text-sm">{{ app()->getLocale() === 'id' ? 'English' : 'Bahasa Indonesia' }}</a>
        </nav>
    </header>
    <noscript><nav aria-label="{{ __('common.nav.menu') }}" class="mx-auto flex w-full max-w-page flex-wrap gap-3 px-4 py-3">@foreach($navItems as [$route, $matches, $label])<a href="{{ route($route) }}" class="flex min-h-11 items-center text-sm text-link">{{ $label }}</a>@endforeach</nav></noscript>
    <main id="main" tabindex="-1" class="mx-auto w-full max-w-page flex-1 px-4 py-6 sm:px-6">
        @if($previewMode ?? false)<p class="mb-4 rounded-lg border border-control bg-surface p-3 text-sm">Local preview · Test fixtures · No production data</p>@endif
        @if(($footballUnavailable ?? false) && !request()->routeIs('football.matchday'))<p role="alert" class="mb-4 rounded-lg border border-danger bg-surface p-3 text-sm text-danger">{{ __('common.empty.unavailable') }}</p>@endif
        @yield('content')
    </main>
    <footer class="mt-8 border-t border-divider py-6 text-xs text-muted">
        <div class="mx-auto flex max-w-page flex-wrap items-center justify-between gap-4 px-4 sm:px-6">
            <span>PitchTicker · {{ __('common.footer.rights', ['year' => date('Y')]) }}</span>
            <div class="flex flex-wrap gap-4">
                <a href="{{ route('landing') }}" class="inline-flex min-h-11 items-center hover:text-link">{{ __('landing.about') }}</a>
                <a href="{{ route('football.compare') }}" class="inline-flex min-h-11 items-center hover:text-link">{{ __('common.nav.compare') }}</a>
                <a href="{{ route('upload.create') }}" class="inline-flex min-h-11 items-center hover:text-link">{{ __('common.nav.upload') }}</a>
                @auth <a href="{{ route('admin.dashboard') }}" class="inline-flex min-h-11 items-center hover:text-link">{{ __('common.nav.admin_panel') }}</a> @else <a href="{{ route('login') }}" class="inline-flex min-h-11 items-center hover:text-link">{{ __('auth.heading') }}</a> @endauth
            </div>
        </div>
    </footer>
</body>
</html>
