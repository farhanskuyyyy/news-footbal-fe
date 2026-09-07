<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-ink text-white selection:bg-primary selection:text-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'KREASIBALL — '.__('common.brand.tagline'))</title>

    <!-- Fonts: DM Sans for headings, Inter for body -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS & Alpine.js CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                        display: ['"DM Sans"', 'Inter', 'sans-serif'],
                        mono: ['ui-monospace', 'SFMono-Regular', 'Menlo', 'monospace'],
                    },
                    colors: {
                        primary: '#8B1E2D',
                        accent: '#E63946',
                        gold: '#F4D35E',
                        steel: '#457B9D',
                        ink: '#000000',
                        surface: '#111111',
                        line: '#333333',
                        body: '#999999',
                        muted: '#6b7280',
                    },
                    maxWidth: {
                        page: '1200px',
                    },
                },
            },
        }
    </script>

    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }
        h1, h2, h3, h4 { font-family: 'DM Sans', Inter, sans-serif; }
        [x-cloak] { display: none !important; }
        /* Small uppercase eyebrow above section headings */
        .kicker { letter-spacing: 0.18em; }
    </style>

</head>
<body class="flex min-h-full flex-col bg-ink text-white antialiased">

    {{-- Top Broadcast Navigation --}}
    <header x-data="{ mobileOpen: false }" class="sticky top-0 z-50 border-b border-line bg-ink">
        <div class="mx-auto flex w-full max-w-page items-center justify-between px-4 sm:px-6 py-4">
            <div class="flex items-center gap-8">
                {{-- Logo / Crest --}}
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 group">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg ">
                        <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5 text-white">
                            <circle cx="12" cy="12" r="9" fill="currentColor" opacity="0.15"/>
                            <path d="M12 3l1.9 1.4-.7 2.2h-2.4l-.7-2.2L12 3zM4.8 8.6l2.3.1.7 2.2-1.9 1.4-1.9-1.4.8-2.3zm14.4 0l.8 2.3-1.9 1.4-1.9-1.4.7-2.2 2.3-.1zM8.2 18.4l-.7-2.2 1.9-1.4 1.9 1.4-.7 2.2H8.2zm7.6 0h-2.4l-.7-2.2 1.9-1.4 1.9 1.4-.7 2.2z" fill="currentColor"/>
                        </svg>
                    </div>
                    <div>
                        <span class="flex items-center gap-2 font-display text-lg font-bold tracking-tight text-white">
                            KREASI<span class="text-accent">BALL</span>
                            <span class="rounded-lg border border-line px-1.5 py-0.5 text-xs font-medium uppercase tracking-widest text-body">PRO</span>
                        </span>
                    </div>
                </a>

                {{-- Nav Links (underline active-state, inline SVG icons) --}}
                <nav class="hidden lg:flex items-center gap-1 text-sm font-semibold">
                    @php
                        $navItems = [
                            ['route' => 'home', 'match' => ['home'], 'label' => __('common.nav.home'),
                             'icon' => '<path d="M4 11.5L12 4l8 7.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/><path d="M6 10v9h12v-9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>'],
                            ['route' => 'football.index', 'match' => ['football.index', 'football.team', 'football.fixture', 'football.player'], 'label' => __('common.nav.portal'),
                             'icon' => '<circle cx="12" cy="12" r="8.5" stroke="currentColor" stroke-width="1.6"/><path d="M12 6.5l3.5 2.6-1.3 4.1h-4.4L8 9.1 12 6.5z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/>'],
                            ['route' => 'football.live', 'match' => ['football.live'], 'label' => __('common.nav.live'), 'live' => true,
                             'icon' => '<circle cx="12" cy="12" r="8.5" stroke="currentColor" stroke-width="1.6"/><path d="M12 7v5l3 2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>'],
                            ['route' => 'football.matchday', 'match' => ['football.matchday'], 'label' => __('common.nav.schedule'),
                             'icon' => '<rect x="4" y="5.5" width="16" height="14" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M4 9.5h16M8 3.5v4M16 3.5v4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>'],
                            ['route' => 'football.transfers', 'match' => ['football.transfers'], 'label' => __('common.nav.transfers'),
                             'icon' => '<path d="M4 8h13m0 0l-3-3m3 3l-3 3M20 16H7m0 0l3-3m-3 3l3 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>'],
                            ['route' => 'news.index', 'match' => ['news.*'], 'label' => __('common.nav.news'),
                             'icon' => '<rect x="4" y="5" width="16" height="14" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M7.5 9h6M7.5 12h6M7.5 15h4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>'],
                            ['route' => 'upload.create', 'match' => ['upload.*'], 'label' => __('common.nav.upload'),
                             'icon' => '<path d="M12 15V5m0 0l-3.5 3.5M12 5l3.5 3.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/><path d="M5 15v2.5A1.5 1.5 0 006.5 19h11a1.5 1.5 0 001.5-1.5V15" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>'],
                        ];
                    @endphp
                    @foreach($navItems as $n)
                        @php $active = request()->routeIs(...$n['match']); @endphp
                        <a href="{{ route($n['route']) }}"
                           class="group flex items-center gap-2 px-3 py-2 text-sm transition-colors {{ $active ? 'font-semibold text-white' : 'text-body hover:text-white' }}">
                            @if(!empty($n['live']))
                                <span class="h-2 w-2 rounded-full bg-accent"></span>
                            @else
                                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 {{ $active ? 'text-accent' : 'text-muted group-hover:text-body' }}">{!! $n['icon'] !!}</svg>
                            @endif
                            <span class="whitespace-nowrap">{{ $n['label'] }}</span>
                        </a>
                    @endforeach
                </nav>
            </div>

            {{-- Search + Live Status --}}
            <div class="flex items-center gap-3 text-xs font-medium">
                {{-- Language switcher (session-backed, keeps the current URL) --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" @click.outside="open = false" type="button"
                            aria-label="{{ __('common.locale.label') }}"
                            class="flex h-9 items-center gap-1.5 rounded-lg border border-line px-3 text-xs font-medium uppercase tracking-wider text-body transition-colors hover:text-white">
                        <svg viewBox="0 0 24 24" fill="none" class="h-3.5 w-3.5"><circle cx="12" cy="12" r="8.5" stroke="currentColor" stroke-width="1.6"/><path d="M3.5 12h17M12 3.5c2.2 2.4 3.3 5.4 3.3 8.5s-1.1 6.1-3.3 8.5c-2.2-2.4-3.3-5.4-3.3-8.5S9.8 5.9 12 3.5z" stroke="currentColor" stroke-width="1.4"/></svg>
                        {{ app()->getLocale() }}
                    </button>
                    <div x-show="open" x-cloak x-transition.opacity
                         class="absolute right-0 z-50 mt-2 w-44 overflow-hidden rounded-xl border border-line bg-surface py-1">
                        @foreach(\App\Http\Middleware\SetLocale::SUPPORTED as $code => $native)
                            <a href="{{ route('locale.switch', $code) }}"
                               class="flex items-center justify-between gap-2 px-3.5 py-2 text-xs transition-colors {{ app()->getLocale() === $code ? 'font-semibold text-accent' : 'text-body hover:text-white' }}">
                                {{ $native }}
                                <span class="font-mono text-xs uppercase text-muted">{{ $code }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <a href="{{ route('football.search') }}" title="{{ __('common.nav.search_title') }}"
                   class="flex h-9 w-9 items-center justify-center rounded-lg border border-line text-body transition-colors hover:text-white">
                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.8"/><path d="M20 20l-3.5-3.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                </a>
                @auth
                    <a href="{{ route('admin.dashboard') }}" title="{{ __('common.nav.admin_panel') }}"
                       class="flex h-9 w-9 items-center justify-center rounded-lg border border-line text-accent transition-colors hover:text-white">
                        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M12 15a3 3 0 100-6 3 3 0 000 6z" stroke="currentColor" stroke-width="1.6"/><path d="M19.4 15a1.7 1.7 0 00.3 1.9l.1.1a2 2 0 11-2.8 2.8l-.1-.1a1.7 1.7 0 00-2.9 1.2V21a2 2 0 11-4 0v-.1a1.7 1.7 0 00-2.9-1.2l-.1.1a2 2 0 11-2.8-2.8l.1-.1a1.7 1.7 0 00-1.2-2.9H2a2 2 0 110-4h.1a1.7 1.7 0 001.2-2.9l-.1-.1a2 2 0 112.8-2.8l.1.1a1.7 1.7 0 001.9.3H8a1.7 1.7 0 001-1.6V2a2 2 0 114 0v.1a1.7 1.7 0 001 1.6 1.7 1.7 0 001.9-.3l.1-.1a2 2 0 112.8 2.8l-.1.1a1.7 1.7 0 00-.3 1.9V8a1.7 1.7 0 001.6 1H22a2 2 0 110 4h-.1a1.7 1.7 0 00-1.6 1z" stroke="currentColor" stroke-width="1.2"/></svg>
                    </a>
                @endauth
                <div class="hidden items-center gap-2 rounded-lg border border-line px-3 py-1.5 sm:flex">
                    <span class="h-2 w-2 rounded-full "></span>
                    <span class="whitespace-nowrap font-mono text-xs tracking-wider text-body">{{ __('common.nav.live_data') }}</span>
                </div>

                {{-- Mobile hamburger --}}
                <button @click="mobileOpen = !mobileOpen" aria-label="{{ __('common.nav.menu') }}"
                        class="flex h-9 w-9 items-center justify-center rounded-lg border border-line text-body transition-colors hover:text-white lg:hidden">
                    <svg x-show="!mobileOpen" viewBox="0 0 24 24" fill="none" class="h-5 w-5"><path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    <svg x-show="mobileOpen" x-cloak viewBox="0 0 24 24" fill="none" class="h-5 w-5"><path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                </button>
            </div>
        </div>
        {{-- Mobile menu panel --}}
        <div x-show="mobileOpen" x-cloak class="border-t border-line bg-ink px-4 py-3 lg:hidden">
            <nav class="flex flex-col gap-1 text-sm font-semibold">
                @foreach($navItems as $n)
                    @php $mActive = request()->routeIs(...$n['match']); @endphp
                    <a href="{{ route($n['route']) }}"
                       class="flex items-center gap-3 rounded-lg px-3.5 py-2.5 transition-colors {{ $mActive ? 'bg-surface text-white' : 'text-body hover:text-white' }}">
                        @if(!empty($n['live']))
                            <span class="h-2 w-2 rounded-full bg-accent"></span>
                        @else
                            <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 {{ $mActive ? 'text-accent' : 'text-muted' }}">{!! $n['icon'] !!}</svg>
                        @endif
                        {{ $n['label'] }}
                    </a>
                @endforeach
            </nav>
        </div>

    </header>

    {{-- Main App View Container --}}
    <main class="mx-auto w-full max-w-page flex-1 px-4 sm:px-6 py-16">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="border-t border-line bg-ink py-10 text-xs text-body">
        {{-- Pitch center-line motif --}}
        <div class="mx-auto w-full max-w-page px-4 sm:px-6">
            <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                <div class="flex items-center gap-2">
                    <span class="font-semibold text-white">{{ __('common.footer.portal') }}</span>
                    <span class="text-muted">/</span>
                    <span>{{ __('common.footer.powered_by') }}</span>
                </div>
                <span>{{ __('common.footer.rights', ['year' => date('Y')]) }}</span>
            </div>
        </div>
    </footer>

</body>
</html>
