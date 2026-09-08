@extends('layouts.app')

@section('title', $item['title'] ?? __('news.detail_title'))

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">

        {{-- Back link --}}
        <a href="{{ route('news.index') }}"
           class="inline-flex items-center gap-1.5 rounded-xl border border-line bg-surface px-3.5 py-2 text-xs font-bold text-body hover:text-accent hover:border-line transition-colors">
            <svg viewBox="0 0 24 24" fill="none" class="w-4 h-4"><path d="M19 12H5M11 18l-6-6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            {{ __('news.back') }}
        </a>

        <article class="rounded-xl border border-line bg-surface p-6 sm:p-8">
            {{-- Meta --}}
            <div class="mb-4 flex flex-wrap items-center gap-3 text-xs">
                <span class="inline-flex items-center rounded-lg px-2.5 py-1 font-bold uppercase tracking-wider text-xs text-accent border border-line">
                    {{ $item['source'] ?? '—' }}
                </span>
                @if (!empty($item['author']))
                    <span class="text-body font-medium">{{ $item['author'] }}</span>
                    <span class="text-muted">•</span>
                @endif
                <time datetime="{{ $item['published_at'] ?? '' }}" class="font-mono text-muted">
                    {{ isset($item['published_at']) ? \Illuminate\Support\Carbon::parse($item['published_at'])->setTimezone('Asia/Jakarta')->locale(app()->getLocale())->translatedFormat('d M Y • H:i') . ' WIB' : '—' }}
                </time>
            </div>

            <h1 class="mb-6 text-2xl sm:text-4xl font-bold leading-tight tracking-tight text-white">
                {{ $item['title'] ?? __('news.untitled') }}
            </h1>

            @if (!empty($item['url_to_image']))
                <div class="mb-6 overflow-hidden rounded-xl border border-line bg-ink">
                    <img src="{{ $item['url_to_image'] }}" alt="{{ $item['title'] ?? '' }}"
                         class="w-full object-cover" loading="lazy"
                         onerror="this.closest('div').style.display='none'">
                </div>
            @endif

            @if (!empty($item['description']))
                <p class="mb-5 text-lg leading-relaxed text-white font-medium border-l-2 border-line pl-4">
                    {{ $item['description'] }}
                </p>
            @endif

            @if (!empty($item['content']))
                <div class="mb-8 whitespace-pre-line leading-relaxed text-white">{{ $item['content'] }}</div>
            @endif

            @if (!empty($item['url']))
                <a href="{{ $item['url'] }}" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 rounded-xl bg-primary hover:bg-accent px-4 py-2.5 text-sm font-bold text-white transition-all">
                    {{ __('news.read_source') }}
                    <svg viewBox="0 0 24 24" fill="none" class="w-4 h-4"><path d="M7 17L17 7M17 7H9M17 7v8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
            @endif
        </article>
    </div>
@endsection
