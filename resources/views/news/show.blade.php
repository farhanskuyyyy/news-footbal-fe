@extends('layouts.app')

@section('title', $item['title'] ?? 'Detail Berita')

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">

        {{-- Back link --}}
        <a href="{{ route('news.index') }}"
           class="inline-flex items-center gap-1.5 rounded-xl border border-slate-800 bg-slate-900 px-3.5 py-2 text-xs font-bold text-slate-400 hover:text-emerald-400 hover:border-slate-700 transition-colors">
            <svg viewBox="0 0 24 24" fill="none" class="w-4 h-4"><path d="M19 12H5M11 18l-6-6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Kembali ke daftar berita
        </a>

        <article class="rounded-3xl border border-slate-800 bg-slate-900/80 p-6 sm:p-8 shadow-2xl">
            {{-- Meta --}}
            <div class="mb-4 flex flex-wrap items-center gap-3 text-xs">
                <span class="inline-flex items-center rounded-md bg-emerald-500/10 px-2.5 py-1 font-bold uppercase tracking-wider text-[10px] text-emerald-400 border border-emerald-500/20">
                    {{ $item['source'] ?? '—' }}
                </span>
                @if (!empty($item['author']))
                    <span class="text-slate-400 font-medium">{{ $item['author'] }}</span>
                    <span class="text-slate-700">•</span>
                @endif
                <time datetime="{{ $item['published_at'] ?? '' }}" class="font-mono text-slate-500">
                    {{ isset($item['published_at']) ? \Illuminate\Support\Carbon::parse($item['published_at'])->translatedFormat('d M Y • H:i') : '—' }}
                </time>
            </div>

            <h1 class="mb-6 text-2xl sm:text-4xl font-black leading-tight tracking-tight text-white">
                {{ $item['title'] ?? 'Tanpa judul' }}
            </h1>

            @if (!empty($item['url_to_image']))
                <div class="mb-6 overflow-hidden rounded-2xl border border-slate-800 bg-slate-950">
                    <img src="{{ $item['url_to_image'] }}" alt="{{ $item['title'] ?? '' }}"
                         class="w-full object-cover" loading="lazy"
                         onerror="this.closest('div').style.display='none'">
                </div>
            @endif

            @if (!empty($item['description']))
                <p class="mb-5 text-lg leading-relaxed text-slate-200 font-medium border-l-2 border-emerald-500/60 pl-4">
                    {{ $item['description'] }}
                </p>
            @endif

            @if (!empty($item['content']))
                <div class="mb-8 whitespace-pre-line leading-relaxed text-slate-300">{{ $item['content'] }}</div>
            @endif

            @if (!empty($item['url']))
                <a href="{{ $item['url'] }}" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 px-4 py-2.5 text-sm font-bold text-slate-950 shadow-lg shadow-emerald-500/20 transition-all">
                    Baca di sumber asli
                    <svg viewBox="0 0 24 24" fill="none" class="w-4 h-4"><path d="M7 17L17 7M17 7H9M17 7v8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
            @endif
        </article>
    </div>
@endsection
