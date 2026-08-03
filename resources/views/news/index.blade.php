@extends('layouts.app')

@section('title', 'Berita Terkini')

@section('content')
    <h1 class="mb-6 text-2xl font-bold">Berita Terkini</h1>

    @if ($news->isEmpty())
        <p class="rounded-lg border border-dashed bg-white p-8 text-center text-gray-500">
            Belum ada berita.
        </p>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($news as $item)
                <article class="flex flex-col rounded-lg border bg-white p-4 shadow-sm transition hover:shadow-md">
                    <div class="mb-2 flex items-center justify-between gap-2 text-xs text-gray-500">
                        <span class="rounded bg-gray-100 px-2 py-0.5 font-medium">{{ $item['source'] ?? '—' }}</span>
                        <time datetime="{{ $item['published_at'] ?? '' }}">
                            {{ isset($item['published_at']) ? \Illuminate\Support\Carbon::parse($item['published_at'])->translatedFormat('d M Y H:i') : '—' }}
                        </time>
                    </div>
                    <h2 class="mb-2 font-semibold leading-snug">
                        <a href="{{ route('news.show', $item['id']) }}" class="hover:text-blue-600">
                            {{ $item['title'] ?? 'Tanpa judul' }}
                        </a>
                    </h2>
                    <p class="mb-4 flex-1 text-sm text-gray-600">
                        {{ \Illuminate\Support\Str::limit($item['description'] ?? '', 120) }}
                    </p>
                    <a href="{{ route('news.show', $item['id']) }}" class="text-sm font-medium text-blue-600 hover:underline">
                        Baca selengkapnya →
                    </a>
                </article>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $news->links() }}
        </div>
    @endif
@endsection
