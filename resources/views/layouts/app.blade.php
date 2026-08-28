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
        /* Custom Football Pitch Pattern */
        .football-pitch-bg {
            background-color: #0d4a22;
            background-image: 
                repeating-linear-gradient(
                    0deg,
                    rgba(255, 255, 255, 0.03),
                    rgba(255, 255, 255, 0.03) 60px,
                    rgba(0, 0, 0, 0.05) 60px,
                    rgba(0, 0, 0, 0.05) 120px
                );
        }
    </style>
</head>
<body class="min-h-full flex flex-col bg-slate-950 text-slate-100 antialiased">

    {{-- Top Broadcast Navigation --}}
    <header class="sticky top-0 z-50 border-b border-slate-800/80 bg-slate-950/80 backdrop-blur-xl">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 sm:px-6 py-3.5">
            <div class="flex items-center gap-8">
                {{-- Logo --}}
                <a href="{{ route('football.index') }}" class="flex items-center gap-2.5 group">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-400 flex items-center justify-center text-slate-950 font-black text-lg shadow-lg shadow-emerald-500/20 group-hover:scale-105 transition-transform">
                        ⚽
                    </div>
                    <div>
                        <span class="text-lg font-black tracking-tight text-white flex items-center gap-1.5">
                            KREASI<span class="text-emerald-400">BALL</span>
                            <span class="text-[9px] font-black uppercase tracking-widest bg-emerald-500/20 text-emerald-300 px-1.5 py-0.5 rounded border border-emerald-500/30">PRO</span>
                        </span>
                    </div>
                </a>

                {{-- Nav Links --}}
                <nav class="hidden md:flex items-center gap-1 text-sm font-semibold">
                    <a href="{{ route('football.index') }}" 
                       class="px-3.5 py-2 rounded-lg transition-all flex items-center gap-2 {{ request()->routeIs('football.*') ? 'bg-slate-800 text-white font-bold border border-slate-700 shadow-inner' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-900' }}">
                        <span>🏆</span> Portal Bola
                    </a>
                    <a href="{{ route('news.index') }}" 
                       class="px-3.5 py-2 rounded-lg transition-all flex items-center gap-2 {{ request()->routeIs('news.*') ? 'bg-slate-800 text-white font-bold border border-slate-700 shadow-inner' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-900' }}">
                        <span>📰</span> Berita Sepak Bola
                    </a>
                    <a href="{{ route('upload.create') }}" 
                       class="px-3.5 py-2 rounded-lg transition-all flex items-center gap-2 {{ request()->routeIs('upload.*') ? 'bg-slate-800 text-white font-bold border border-slate-700 shadow-inner' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-900' }}">
                        <span>📤</span> Upload Gambar
                    </a>
                </nav>
            </div>

            {{-- Live Status Indicator --}}
            <div class="flex items-center gap-4 text-xs font-medium">
                <div class="flex items-center gap-2 bg-slate-900/90 border border-slate-800 px-3 py-1.5 rounded-full">
                    <span class="relative flex h-2 w-2">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span class="text-slate-300 font-mono text-[11px] font-semibold">LIVE DATA READY</span>
                </div>
            </div>
        </div>
    </header>

    {{-- Main App View Container --}}
    <main class="flex-1 mx-auto w-full max-w-7xl px-4 sm:px-6 py-8">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="border-t border-slate-900 bg-slate-950 py-8 mt-12 text-xs text-slate-500">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-2">
                <span class="font-bold text-slate-400">KREASIBALL PORTAL</span>
                <span>•</span>
                <span>Powered by Go Backend REST API & Sportmonks v3</span>
            </div>
            <span>&copy; {{ date('Y') }} Kreasi Farhan. Hak Cipta Dilindungi.</span>
        </div>
    </footer>

</body>
</html>
