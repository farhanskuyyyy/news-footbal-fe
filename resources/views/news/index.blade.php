@extends('layouts.app')

@section('title', 'Berita Sepak Bola Terkini')

@section('content')
    <div class="space-y-8">

        {{-- HEADER --}}
        <div class="pitch-stripes bg-gradient-to-r from-slate-900 via-slate-900/90 to-slate-950 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl relative overflow-hidden">
            <div class="absolute -right-16 -top-16 w-72 h-72 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-6 relative z-10">
                <div>
                    <span class="kicker inline-flex items-center gap-2 text-[10px] font-bold uppercase text-emerald-400 mb-3">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        LIVE FEED
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">Berita Sepak Bola Terkini</h1>
                    <p class="text-xs sm:text-sm text-slate-400 mt-1.5 max-w-lg">
                        Kabar transfer, hasil pertandingan, dan sorotan dari lapangan hijau — diperbarui langsung dari sumber.
                    </p>
                </div>
                <form method="POST" action="{{ route('news.refresh') }}" onsubmit="this.querySelector('button').disabled = true">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 px-4 py-2.5 text-sm font-bold text-slate-950 shadow-lg shadow-emerald-500/20 transition-all disabled:opacity-50 disabled:cursor-wait">
                        <svg viewBox="0 0 24 24" fill="none" class="w-4 h-4"><path d="M4 12a8 8 0 0113.7-5.6M20 12a8 8 0 01-13.7 5.6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M17 3v3.5h-3.5M7 21v-3.5h3.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Refresh Berita
                    </button>
                </form>
            </div>
        </div>

        {{-- FLASH MESSAGES --}}
        @if (session('status'))
            <p class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm font-medium text-emerald-300">
                {{ session('status') }}
            </p>
        @endif
        @if (session('error'))
            <p class="rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm font-medium text-red-300">
                {{ session('error') }}
            </p>
        @endif

        {{-- NEWS GRID --}}
        @if ($news->isEmpty())
            <div class="rounded-3xl border border-dashed border-slate-800 bg-slate-900/40 p-12 text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-950 border border-slate-800 text-emerald-500/70">
                    <svg viewBox="0 0 24 24" fill="none" class="h-7 w-7"><rect x="4" y="5" width="16" height="14" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M7.5 9h6M7.5 12h6M7.5 15h4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                </div>
                <p class="text-base font-bold text-slate-200">Belum ada berita.</p>
                <p class="text-xs text-slate-500 mt-1">Tekan tombol Refresh Berita untuk menarik kabar terbaru.</p>
            </div>
        @else
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($news as $item)
                    <article class="group flex flex-col rounded-2xl border border-slate-800 bg-slate-900/80 overflow-hidden shadow-lg hover:border-slate-700 hover:bg-slate-900 transition-all hover:-translate-y-0.5">
                        @if (!empty($item['url_to_image']))
                            <a href="{{ route('news.show', $item['id']) }}" class="block relative aspect-[16/9] overflow-hidden bg-slate-950">
                                <img src="{{ $item['url_to_image'] }}" alt="{{ $item['title'] ?? '' }}"
                                     class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                                     loading="lazy" onerror="this.closest('a').style.display='none'">
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 to-transparent"></div>
                            </a>
                        @endif

                        <div class="flex flex-1 flex-col p-5">
                            <div class="mb-3 flex items-center justify-between gap-2 text-xs">
                                <span class="inline-flex items-center gap-1.5 rounded-md bg-emerald-500/10 px-2 py-0.5 font-bold uppercase tracking-wider text-[10px] text-emerald-400 border border-emerald-500/20">
                                    {{ $item['source'] ?? '—' }}
                                </span>
                                <time datetime="{{ $item['published_at'] ?? '' }}" class="font-mono text-[11px] text-slate-500">
                                    {{ isset($item['published_at']) ? \Illuminate\Support\Carbon::parse($item['published_at'])->translatedFormat('d M Y • H:i') : '—' }}
                                </time>
                            </div>

                            <h2 class="mb-2 font-extrabold leading-snug text-slate-100">
                                <a href="{{ route('news.show', $item['id']) }}" class="transition-colors group-hover:text-emerald-400">
                                    {{ $item['title'] ?? 'Tanpa judul' }}
                                </a>
                            </h2>

                            <p class="mb-4 flex-1 text-sm leading-relaxed text-slate-400">
                                {{ \Illuminate\Support\Str::limit($item['description'] ?? '', 120) }}
                            </p>

                            <a href="{{ route('news.show', $item['id']) }}" class="mt-auto inline-flex items-center gap-1.5 text-sm font-bold text-emerald-400 hover:gap-2.5 transition-all">
                                Baca selengkapnya
                                <svg viewBox="0 0 24 24" fill="none" class="w-4 h-4"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-8 [&_nav]:flex [&_nav]:justify-center [&_a]:text-slate-300 [&_span]:text-slate-500">
                {{ $news->links() }}
            </div>
        @endif
    </div>
@endsection
