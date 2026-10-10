<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="bg-canvas text-foreground">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PitchTicker · {{ __('landing.title') }}</title>
    <meta name="description" content="{{ __('landing.description') }}">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <script>try { document.documentElement.dataset.theme = localStorage.getItem('pitchticker-theme') === 'dark' ? 'dark' : 'light'; } catch { document.documentElement.dataset.theme = 'light'; }</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fonts
</head>
<body class="public-body landing-body bg-canvas text-foreground antialiased">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[100] focus:bg-surface focus:p-3">{{ __('common.nav.skip') }}</a>
    <header class="landing-nav">
        <div class="landing-container flex flex-wrap items-center justify-between gap-3 py-4">
            <a href="{{ route('landing') }}" class="flex min-h-11 items-center gap-2 text-xl font-bold tracking-tight" aria-label="PitchTicker">
                <svg viewBox="0 0 32 32" class="h-8 w-8" aria-hidden="true"><rect width="32" height="32" rx="6" fill="#14532D"/><path d="M9 8v16M9 8h9a5 5 0 0 1 0 10H9" fill="none" stroke="#FFFFFF" stroke-width="3"/><path d="M21 23h5" stroke="#E6B94A" stroke-width="3"/></svg>PitchTicker
            </a>
            <nav aria-label="{{ __('common.nav.menu') }}" class="hidden items-center gap-7 md:flex">
                <a href="#the-game" class="landing-text-link">{{ __('landing.nav_product') }}</a>
                <a href="#competitions" class="landing-text-link">{{ __('landing.nav_leagues') }}</a>
            </nav>
            <div class="flex items-center gap-2">
                <button type="button" data-theme-toggle hidden aria-label="{{ __('common.nav.theme') }}" aria-pressed="false" class="flex h-11 w-11 items-center justify-center rounded-lg"><svg viewBox="0 0 24 24" fill="none" class="h-5 w-5" aria-hidden="true"><circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.6"/><path d="M12 4a8 8 0 0 1 0 16z" fill="currentColor"/></svg></button>
                <a href="{{ route('home') }}" class="landing-button landing-button-small">{{ __('landing.enter') }}</a>
            </div>
        </div>
    </header>
    <main id="main" tabindex="-1">@yield('content')</main>
    <footer class="landing-footer">
        <div class="landing-container flex flex-wrap items-center justify-between gap-4 py-7 text-xs">
            <p>PitchTicker · {{ __('landing.footer') }}</p>
            <div class="flex flex-wrap gap-5">
                <a href="{{ route('news.index') }}" class="landing-text-link">{{ __('common.nav.news') }}</a>
                <a href="{{ route('locale.switch', app()->getLocale() === 'id' ? 'en' : 'id') }}" class="landing-text-link">{{ app()->getLocale() === 'id' ? 'English' : 'Bahasa Indonesia' }}</a>
                <a href="{{ route('home') }}" class="landing-text-link">{{ __('landing.enter') }}</a>
            </div>
        </div>
    </footer>
</body>
</html>
