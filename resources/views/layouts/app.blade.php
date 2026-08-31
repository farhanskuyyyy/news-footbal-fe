<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950 text-slate-100 selection:bg-emerald-500 selection:text-black">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'KREASIBALL - Sports & Football Broadcast')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS & Alpine.js CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        pitch: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            400: '#4ade80',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d',
                            950: '#052e16',
                        },
                        stadium: {
                            800: '#0f172a',
                            850: '#0b1120',
                            900: '#030712',
                            950: '#020617',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        [x-cloak] { display: none !important; }
        /* Chalk pitch-line accent bar under the navbar */
        .pitch-accent {
            background-image: linear-gradient(90deg,
                transparent 0%,
                rgba(16, 185, 129, 0.0) 0%,
                rgba(16, 185, 129, 0.9) 15%,
                rgba(16, 185, 129, 0.9) 85%,
                transparent 100%);
        }
        /* Subtle mown-grass stripes for hero surfaces */
        .pitch-stripes {
            background-image: repeating-linear-gradient(
                115deg,
                rgba(16, 185, 129, 0.05),
                rgba(16, 185, 129, 0.05) 40px,
                rgba(255, 255, 255, 0.015) 40px,
                rgba(255, 255, 255, 0.015) 80px
            );
        }
        /* Kicker label — the little uppercase eyebrow above headings */
        .kicker {
            font-family: 'JetBrains Mono', monospace;
            letter-spacing: 0.28em;
        }
    </style>
</head>
<body class="min-h-full flex flex-col bg-slate-950 text-slate-100 antialiased">

    {{-- Top Broadcast Navigation --}}
    <header x-data="{ mobileOpen: false }" class="sticky top-0 z-50 bg-slate-950/85 backdrop-blur-xl">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 sm:px-6 py-3.5">
            <div class="flex items-center gap-8">
                {{-- Logo / Crest --}}
                <a href="{{ route('football.index') }}" class="flex items-center gap-2.5 group">
                    <div class="relative w-9 h-9 rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-400 flex items-center justify-center shadow-lg shadow-emerald-500/25 group-hover:scale-105 transition-transform ring-1 ring-emerald-300/30">
                        <svg viewBox="0 0 24 24" fill="none" class="w-5 h-5 text-slate-950">
                            <circle cx="12" cy="12" r="9" fill="currentColor" opacity="0.15"/>
                            <path d="M12 3l1.9 1.4-.7 2.2h-2.4l-.7-2.2L12 3zM4.8 8.6l2.3.1.7 2.2-1.9 1.4-1.9-1.4.8-2.3zm14.4 0l.8 2.3-1.9 1.4-1.9-1.4.7-2.2 2.3-.1zM8.2 18.4l-.7-2.2 1.9-1.4 1.9 1.4-.7 2.2H8.2zm7.6 0h-2.4l-.7-2.2 1.9-1.4 1.9 1.4-.7 2.2z" fill="currentColor"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-lg font-black tracking-tight text-white flex items-center gap-1.5">
                            KREASI<span class="text-emerald-400">BALL</span>
                            <span class="text-[9px] font-black uppercase tracking-widest bg-emerald-500/15 text-emerald-300 px-1.5 py-0.5 rounded border border-emerald-500/30">PRO</span>
                        </span>
                    </div>
                </a>

                {{-- Nav Links (underline active-state, inline SVG icons) --}}
                <nav class="hidden md:flex items-center gap-1 text-sm font-semibold">
                    @php
                        $navItems = [
                            ['route' => 'football.index', 'match' => ['football.index', 'football.team', 'football.fixture', 'football.player'], 'label' => 'Portal Bola',
                             'icon' => '<circle cx="12" cy="12" r="8.5" stroke="currentColor" stroke-width="1.6"/><path d="M12 6.5l3.5 2.6-1.3 4.1h-4.4L8 9.1 12 6.5z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/>'],
                            ['route' => 'football.live', 'match' => ['football.live'], 'label' => 'Live', 'live' => true,
                             'icon' => '<circle cx="12" cy="12" r="8.5" stroke="currentColor" stroke-width="1.6"/><path d="M12 7v5l3 2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>'],
                            ['route' => 'football.matchday', 'match' => ['football.matchday'], 'label' => 'Jadwal',
                             'icon' => '<rect x="4" y="5.5" width="16" height="14" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M4 9.5h16M8 3.5v4M16 3.5v4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>'],
                            ['route' => 'news.index', 'match' => ['news.*'], 'label' => 'Berita',
                             'icon' => '<rect x="4" y="5" width="16" height="14" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M7.5 9h6M7.5 12h6M7.5 15h4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>'],
                            ['route' => 'upload.create', 'match' => ['upload.*'], 'label' => 'Upload',
                             'icon' => '<path d="M12 15V5m0 0l-3.5 3.5M12 5l3.5 3.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/><path d="M5 15v2.5A1.5 1.5 0 006.5 19h11a1.5 1.5 0 001.5-1.5V15" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>'],
                        ];
                    @endphp
                    @foreach($navItems as $n)
                        @php $active = request()->routeIs(...$n['match']); @endphp
                        <a href="{{ route($n['route']) }}"
                           class="group relative px-3.5 py-2 flex items-center gap-2 transition-colors {{ $active ? 'text-white' : 'text-slate-400 hover:text-slate-100' }}">
                            @if(!empty($n['live']))
                                <span class="relative flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-500 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                                </span>
                            @else
                                <svg viewBox="0 0 24 24" fill="none" class="w-4 h-4 {{ $active ? 'text-emerald-400' : 'text-slate-500 group-hover:text-slate-300' }}">{!! $n['icon'] !!}</svg>
                            @endif
                            <span>{{ $n['label'] }}</span>
                            <span class="absolute inset-x-2.5 -bottom-[15px] h-0.5 rounded-full transition-all {{ $active ? 'bg-emerald-400' : 'bg-transparent group-hover:bg-slate-700' }}"></span>
                        </a>
                    @endforeach
                </nav>
            </div>

            {{-- Search + Live Status --}}
            <div class="flex items-center gap-3 text-xs font-medium">
                <a href="{{ route('football.search') }}" title="Cari klub / pemain / liga"
                   class="flex h-9 w-9 items-center justify-center rounded-full border border-slate-800 bg-slate-900/90 text-slate-400 hover:text-emerald-400 hover:border-slate-700 transition-colors">
                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.8"/><path d="M20 20l-3.5-3.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                </a>
                @auth
                    <a href="{{ route('admin.dashboard') }}" title="Admin Panel"
                       class="flex h-9 w-9 items-center justify-center rounded-full border border-emerald-500/30 bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20 transition-colors">
                        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M12 15a3 3 0 100-6 3 3 0 000 6z" stroke="currentColor" stroke-width="1.6"/><path d="M19.4 15a1.7 1.7 0 00.3 1.9l.1.1a2 2 0 11-2.8 2.8l-.1-.1a1.7 1.7 0 00-2.9 1.2V21a2 2 0 11-4 0v-.1a1.7 1.7 0 00-2.9-1.2l-.1.1a2 2 0 11-2.8-2.8l.1-.1a1.7 1.7 0 00-1.2-2.9H2a2 2 0 110-4h.1a1.7 1.7 0 001.2-2.9l-.1-.1a2 2 0 112.8-2.8l.1.1a1.7 1.7 0 001.9.3H8a1.7 1.7 0 001-1.6V2a2 2 0 114 0v.1a1.7 1.7 0 001 1.6 1.7 1.7 0 001.9-.3l.1-.1a2 2 0 112.8 2.8l-.1.1a1.7 1.7 0 00-.3 1.9V8a1.7 1.7 0 001.6 1H22a2 2 0 110 4h-.1a1.7 1.7 0 00-1.6 1z" stroke="currentColor" stroke-width="1.2"/></svg>
                    </a>
                @endauth
                <div class="hidden sm:flex items-center gap-2 bg-slate-900/90 border border-slate-800 px-3 py-1.5 rounded-full">
                    <span class="relative flex h-2 w-2">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span class="text-slate-300 font-mono text-[11px] font-semibold tracking-wider">LIVE DATA</span>
                </div>

                {{-- Mobile hamburger --}}
                <button @click="mobileOpen = !mobileOpen" aria-label="Menu"
                        class="md:hidden flex h-9 w-9 items-center justify-center rounded-full border border-slate-800 bg-slate-900/90 text-slate-300 hover:text-emerald-400 transition-colors">
                    <svg x-show="!mobileOpen" viewBox="0 0 24 24" fill="none" class="h-5 w-5"><path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    <svg x-show="mobileOpen" x-cloak viewBox="0 0 24 24" fill="none" class="h-5 w-5"><path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                </button>
            </div>
        </div>
        {{-- Mobile menu panel --}}
        <div x-show="mobileOpen" x-cloak x-transition.opacity class="md:hidden border-t border-slate-800/80 bg-slate-950/95 px-4 py-3">
            <nav class="flex flex-col gap-1 text-sm font-semibold">
                @foreach($navItems as $n)
                    @php $mActive = request()->routeIs(...$n['match']); @endphp
                    <a href="{{ route($n['route']) }}"
                       class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 transition-colors {{ $mActive ? 'bg-slate-900 text-white' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-900' }}">
                        @if(!empty($n['live']))
                            <span class="flex h-2 w-2 rounded-full bg-red-500"></span>
                        @else
                            <svg viewBox="0 0 24 24" fill="none" class="w-4 h-4 {{ $mActive ? 'text-emerald-400' : 'text-slate-500' }}">{!! $n['icon'] !!}</svg>
                        @endif
                        {{ $n['label'] }}
                    </a>
                @endforeach
            </nav>
        </div>

        {{-- Chalk pitch-line accent --}}
        <div class="h-px w-full bg-slate-800/80"></div>
        <div class="pitch-accent h-0.5 w-full opacity-80"></div>
    </header>

    {{-- Main App View Container --}}
    <main class="flex-1 mx-auto w-full max-w-7xl px-4 sm:px-6 py-8">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="mt-12 border-t border-slate-900 bg-slate-950 py-8 text-xs text-slate-500">
        {{-- Pitch center-line motif --}}
        <div class="mx-auto max-w-7xl px-4 sm:px-6">
            <div class="mb-6 flex items-center gap-4">
                <span class="h-px flex-1 bg-slate-900"></span>
                <span class="flex h-6 w-6 items-center justify-center rounded-full border border-slate-800 text-emerald-500/70">
                    <svg viewBox="0 0 24 24" fill="none" class="h-3.5 w-3.5"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.6"/></svg>
                </span>
                <span class="h-px flex-1 bg-slate-900"></span>
            </div>
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-2">
                    <span class="font-bold text-slate-400">KREASIBALL PORTAL</span>
                    <span class="text-slate-700">•</span>
                    <span>Powered by Go Backend REST API &amp; Sportmonks v3</span>
                </div>
                <span>&copy; {{ date('Y') }} Kreasi Farhan. Hak Cipta Dilindungi.</span>
            </div>
        </div>
    </footer>

</body>
</html>
