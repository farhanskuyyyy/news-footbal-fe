<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-canvas text-foreground">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', __('cms.overview')) · PitchTicker CMS</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <script>
        try { document.documentElement.dataset.theme = localStorage.getItem('pitchticker-theme') === 'dark' ? 'dark' : 'light'; }
        catch { document.documentElement.dataset.theme = 'light'; }
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fonts
</head>
<body class="cms-body min-h-full bg-canvas text-foreground antialiased" data-cms-status="{{ route('admin.status') }}" data-cms-unknown="{{ __('cms.unknown') }}">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[100] focus:bg-surface focus:p-3">{{ __('common.nav.skip') }}</a>
    <aside class="cms-sidebar">@include('admin.partials.navigation')</aside>
    <div class="cms-mobile-header">
        <span class="font-bold">PitchTicker <span class="ml-1 text-xs font-medium text-muted">CMS</span></span>
        <button type="button" hidden class="cms-button" data-cms-open aria-controls="cms-navigation" aria-expanded="false">{{ __('cms.menu') }}</button>
    </div>
    <dialog id="cms-navigation" class="cms-drawer" aria-label="{{ __('cms.navigation') }}">
        <form method="dialog" class="mb-3 flex justify-end"><button class="cms-button">{{ __('cms.close') }}</button></form>
        @include('admin.partials.navigation')
    </dialog>
    <noscript><div class="cms-noscript">@include('admin.partials.navigation')</div></noscript>
    <main id="main" tabindex="-1" class="cms-main">
        @if($previewMode ?? false)<p class="mb-5 border border-control p-3 text-sm">Local preview · Test fixtures · No production data</p>@endif
        <header class="cms-page-heading">
            <div class="min-w-0">
                <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">@yield('heading', __('cms.overview'))</h1>
                @hasSection('description')<p class="mt-2 max-w-2xl text-sm leading-relaxed text-muted">@yield('description')</p>@endif
            </div>
            @hasSection('actions')<div class="flex flex-wrap gap-2">@yield('actions')</div>@endif
        </header>
        @if(session('status'))<p role="status" class="mb-6 border border-line bg-surface p-3 text-sm text-link">{{ session('status') }}</p>@endif
        @if(session('error'))<p role="alert" class="mb-6 border border-danger bg-surface p-3 text-sm text-danger">{{ session('error') }}</p>@endif
        @if($errors->any())<p role="alert" class="mb-6 border border-danger bg-surface p-3 text-sm text-danger">{{ __('cms.validation') }}</p>@endif
        @yield('content')
    </main>
    @stack('scripts')
</body>
</html>
