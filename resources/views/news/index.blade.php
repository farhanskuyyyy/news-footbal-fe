@extends('layouts.app')

@section('title', __('news.title'))

@section('content')
    <div class="space-y-8">

        {{-- HEADER --}}
        <div class="relative overflow-hidden border border-line rounded-xl p-6 sm:p-8 relative overflow-hidden">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-6 relative z-10">
                <div>
                    <span class="kicker inline-flex items-center gap-2 text-xs font-bold uppercase text-primary mb-3">
                        <span class="relative flex h-2 w-2">
                            <span class=" absolute inline-flex h-full w-full rounded-lg opacity-75"></span>
                            <span class="relative inline-flex rounded-lg h-2 w-2 "></span>
                        </span>
                        {{ __('news.kicker') }}
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">{{ __('news.heading') }}</h1>
                    <p class="text-xs sm:text-sm text-body mt-1.5 max-w-lg">
                        {{ __('news.subheading') }}
                    </p>
                </div>
                <form method="POST" action="{{ route('news.refresh') }}" onsubmit="this.querySelector('button').disabled = true">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-primary hover:bg-accent px-4 py-2.5 text-sm font-bold text-white transition-all disabled:opacity-50 disabled:cursor-wait">
                        <svg viewBox="0 0 24 24" fill="none" class="w-4 h-4"><path d="M4 12a8 8 0 0113.7-5.6M20 12a8 8 0 01-13.7 5.6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M17 3v3.5h-3.5M7 21v-3.5h3.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        {{ __('news.refresh') }}
                    </button>
                </form>
            </div>
        </div>

        {{-- FLASH MESSAGES --}}
        @if (session('status'))
            <p class="rounded-xl border border-line px-4 py-3 text-sm font-medium text-accent">
                {{ session('status') }}
            </p>
        @endif
        @if (session('error'))
            <p class="rounded-xl border border-line px-4 py-3 text-sm font-medium text-accent">
                {{ session('error') }}
            </p>
        @endif

        {{-- NEWS GRID --}}
        @if ($news->isEmpty())
            <div class="rounded-xl border border-dashed border-line bg-surface p-12 text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-xl bg-ink border border-line text-accent">
                    <svg viewBox="0 0 24 24" fill="none" class="h-7 w-7"><rect x="4" y="5" width="16" height="14" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M7.5 9h6M7.5 12h6M7.5 15h4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                </div>
                <p class="text-base font-bold text-white">{{ __('news.empty') }}</p>
                <p class="text-xs text-muted mt-1">{{ __('news.empty_hint') }}</p>
            </div>
        @else
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($news as $item)
                    <article class="group flex flex-col rounded-xl border border-line bg-surface overflow-hidden hover:border-line hover:bg-surface transition-all">
                        @if (!empty($item['url_to_image']))
                            <a href="{{ route('news.show', $item['id']) }}" class="block relative aspect-[16/9] overflow-hidden bg-ink">
                                <img src="{{ $item['url_to_image'] }}" alt="{{ $item['title'] ?? '' }}"
                                     class="h-full w-full object-cover duration-500 group-"
                                     loading="lazy" onerror="this.closest('a').style.display='none'">
                            </a>
                        @endif

                        <div class="flex flex-1 flex-col p-5">
                            <div class="mb-3 flex items-center justify-between gap-2 text-xs">
                                <span class="inline-flex items-center gap-1.5 rounded-lg px-2 py-0.5 font-bold uppercase tracking-wider text-xs text-accent border border-line">
                                    {{ $item['source'] ?? '—' }}
                                </span>
                                <time datetime="{{ $item['published_at'] ?? '' }}" class="font-mono text-xs text-muted">
                                    {{ isset($item['published_at']) ? \Illuminate\Support\Carbon::parse($item['published_at'])->setTimezone('Asia/Jakarta')->locale(app()->getLocale())->translatedFormat('d M Y • H:i') . ' WIB' : '—' }}
                                </time>
                            </div>

                            <h2 class="mb-2 font-semibold leading-snug text-white">
                                <a href="{{ route('news.show', $item['id']) }}" class="transition-colors group-hover:text-accent">
                                    {{ $item['title'] ?? __('news.untitled') }}
                                </a>
                            </h2>

                            <p class="mb-4 flex-1 text-sm leading-relaxed text-body">
                                {{ \Illuminate\Support\Str::limit($item['description'] ?? '', 120) }}
                            </p>

                            <a href="{{ route('news.show', $item['id']) }}" class="mt-auto inline-flex items-center gap-1.5 text-sm font-bold text-accent hover:gap-2.5 transition-all">
                                {{ __('news.read_more') }}
                                <svg viewBox="0 0 24 24" fill="none" class="w-4 h-4"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-8 [&_nav]:flex [&_nav]:justify-center [&_a]:text-white [&_span]:text-muted">
                {{ $news->links() }}
            </div>
        @endif
    </div>
@endsection
