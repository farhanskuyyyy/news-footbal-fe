@extends('layouts.app')

@section('title', __('football.live.title'))

@section('content')
    {{-- Auto-refresh every 30s while viewing the live board --}}
    <meta http-equiv="refresh" content="30">

    <div class="space-y-8">
        {{-- Header --}}
        <div class="relative overflow-hidden border border-line rounded-xl p-6 sm:p-8 relative overflow-hidden">
            <div class="relative z-10">
                <span class="kicker inline-flex items-center gap-2 text-xs font-bold uppercase text-primary mb-3">
                    <span class="relative flex h-2 w-2">
                        <span class=" absolute inline-flex h-full w-full rounded-lg opacity-75"></span>
                        <span class="relative inline-flex rounded-lg h-2 w-2 "></span>
                    </span>
                    {{ __('football.live.kicker') }}
                </span>
                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">{{ __('football.live.heading') }}</h1>
                <p class="text-xs sm:text-sm text-body mt-1.5">{{ __('football.live.subheading') }}</p>
            </div>
        </div>

        @if(count($matches) > 0)
            <div class="grid gap-4 md:grid-cols-2">
                @foreach($matches as $f)
                    @include('football.partials.live-card', ['f' => $f])
                @endforeach
            </div>
        @else
            <div class="rounded-xl border border-dashed border-line bg-surface p-12 text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-xl bg-ink border border-line text-muted">
                    <svg viewBox="0 0 24 24" fill="none" class="icon h-7 w-7"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.6"/><path d="M12 7v5l3 2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <p class="text-base font-bold text-white">{{ __('football.live.empty') }}</p>
                <p class="text-xs text-muted mt-1">{!! __('football.live.empty_hint', ['link' => '<a href="'.route('football.matchday').'" class="text-accent hover:underline">'.e(__('football.live.empty_link')).'</a>']) !!}</p>
            </div>
        @endif
    </div>
@endsection
