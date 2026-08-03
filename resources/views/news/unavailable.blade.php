@extends('layouts.app')

@section('title', 'Layanan Tidak Tersedia')

@section('content')
    <div class="mx-auto max-w-md rounded-lg border bg-white p-8 text-center shadow-sm">
        <div class="mb-4 text-5xl">⚠️</div>
        <h1 class="mb-2 text-xl font-bold">Berita sedang tidak tersedia</h1>
        <p class="mb-6 text-gray-600">
            Kami tidak dapat mengambil data berita saat ini. Silakan coba lagi beberapa saat lagi.
        </p>
        <a href="{{ route('news.index') }}" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
            Coba lagi
        </a>
    </div>
@endsection
