@extends('layouts.app')

@section('title', $item['title'] ?? 'Detail Berita')

@section('content')
    <a href="{{ route('news.index') }}" class="mb-6 inline-block text-sm text-blue-600 hover:underline">← Kembali ke daftar</a>

    <article class="rounded-lg border bg-white p-6 shadow-sm">
        <div class="mb-3 flex flex-wrap items-center gap-3 text-sm text-gray-500">
            <span class="rounded bg-gray-100 px-2 py-0.5 font-medium">{{ $item['source'] ?? '—' }}</span>
            @if (!empty($item['author']))
                <span>{{ $item['author'] }}</span>
            @endif
            <time datetime="{{ $item['published_at'] ?? '' }}">
                {{ isset($item['published_at']) ? \Illuminate\Support\Carbon::parse($item['published_at'])->translatedFormat('d M Y H:i') : '—' }}
            </time>
        </div>

        <h1 class="mb-4 text-3xl font-bold leading-tight">{{ $item['title'] ?? 'Tanpa judul' }}</h1>

        @if (!empty($item['url_to_image']))
            <img src="{{ $item['url_to_image'] }}" alt="{{ $item['title'] ?? '' }}"
                 class="mb-6 w-full rounded-lg object-cover" loading="lazy"
                 onerror="this.style.display='none'">
        @endif

        @if (!empty($item['description']))
            <p class="mb-4 text-lg text-gray-700">{{ $item['description'] }}</p>
        @endif

        @if (!empty($item['content']))
            <div class="mb-6 whitespace-pre-line leading-relaxed text-gray-800">{{ $item['content'] }}</div>
        @endif

        @if (!empty($item['url']))
            <a href="{{ $item['url'] }}" target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center gap-1 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                Baca di sumber asli ↗
            </a>
        @endif
    </article>
@endsection
