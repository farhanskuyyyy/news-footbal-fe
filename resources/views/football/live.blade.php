@extends('layouts.app')

@section('title', __('football.live.title'))

@section('content')
    {{-- Auto-refresh every 30s while viewing the live board --}}
    <meta http-equiv="refresh" content="30">

    <div class="space-y-8">
        {{-- Header --}}
        <div class="pitch-stripes bg-gradient-to-r from-slate-900 via-slate-900/90 to-slate-950 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl relative overflow-hidden">
            <div class="absolute -right-16 -top-16 w-72 h-72 bg-red-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="relative z-10">
                <span class="kicker inline-flex items-center gap-2 text-[10px] font-bold uppercase text-red-400 mb-3">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-500 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                    </span>
                    {{ __('football.live.kicker') }}
                </span>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">{{ __('football.live.heading') }}</h1>
                <p class="text-xs sm:text-sm text-slate-400 mt-1.5">{{ __('football.live.subheading') }}</p>
            </div>
        </div>

        @if(count($matches) > 0)
            <div class="grid gap-4 md:grid-cols-2">
                @foreach($matches as $f)
                    @include('football.partials.live-card', ['f' => $f])
                @endforeach
            </div>
        @else
            <div class="rounded-3xl border border-dashed border-slate-800 bg-slate-900/40 p-12 text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-950 border border-slate-800 text-slate-500">
                    <svg viewBox="0 0 24 24" fill="none" class="h-7 w-7"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.6"/><path d="M12 7v5l3 2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <p class="text-base font-bold text-slate-200">{{ __('football.live.empty') }}</p>
                <p class="text-xs text-slate-500 mt-1">{!! __('football.live.empty_hint', ['link' => '<a href="'.route('football.matchday').'" class="text-emerald-400 hover:underline">'.e(__('football.live.empty_link')).'</a>']) !!}</p>
            </div>
        @endif
    </div>
@endsection
