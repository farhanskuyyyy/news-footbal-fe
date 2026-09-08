@extends('layouts.app')

@section('title', __('news.unavailable_title'))

@section('content')
    <div class="mx-auto max-w-md rounded-xl border border-line bg-surface p-8 text-center">
        <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-xl border border-line text-gold">
            <svg viewBox="0 0 24 24" fill="none" class="icon h-8 w-8"><path d="M12 8v5M12 16.5h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M10.3 3.9L2.5 17.5A2 2 0 004.2 20.5h15.6a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
        </div>
        <h1 class="mb-2 text-xl font-bold text-white">{{ __('news.unavailable_heading') }}</h1>
        <p class="mb-6 text-sm text-body">
            {{ __('news.unavailable_text') }}
        </p>
        <a href="{{ route('news.index') }}"
           class="inline-flex items-center gap-2 rounded-xl bg-primary hover:bg-accent px-4 py-2.5 text-sm font-bold text-white transition-all">
            <svg viewBox="0 0 24 24" fill="none" class="icon w-4 h-4"><path d="M4 12a8 8 0 0113.7-5.6M20 12a8 8 0 01-13.7 5.6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M17 3v3.5h-3.5M7 21v-3.5h3.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            {{ __('news.retry') }}
        </a>
    </div>
@endsection
