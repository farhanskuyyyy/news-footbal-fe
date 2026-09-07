@extends('layouts.app')

@section('title', __('football.matchday.title'))

@php
    $today = date('Y-m-d');
    $prev = date('Y-m-d', strtotime($date.' -1 day'));
    $next = date('Y-m-d', strtotime($date.' +1 day'));
    // Group fixtures by league name for readability. $fixtures already arrives
    // with CMS-enabled leagues (status = true) first, and groupBy keeps that
    // order, so the enabled leagues stay at the top of the page.
    $byLeague = collect($fixtures)->groupBy(fn ($f) => $f['league']['name'] ?? 'Lainnya');
    $enabledIds = collect($enabledLeagueIds ?? []);
@endphp

@section('content')
    <div class="space-y-8">
        {{-- Header + date navigator --}}
        <div class="relative overflow-hidden border border-line rounded-xl p-6 sm:p-8 relative overflow-hidden">
            <div class="relative z-10 flex flex-col sm:flex-row sm:items-end justify-between gap-6">
                <div>
                    <span class="kicker inline-block text-xs font-bold uppercase text-primary mb-3">{{ __('football.matchday.kicker') }}</span>
                    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">
                        {{ $date === $today ? __('football.matchday.today') : \Illuminate\Support\Carbon::parse($date)->locale(app()->getLocale())->translatedFormat('l, d F Y') }}
                    </h1>
                    <p class="text-xs sm:text-sm text-body mt-1.5 font-mono">{{ trans_choice('football.matchday.count', count($fixtures), ['count' => count($fixtures)]) }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('football.matchday', ['date' => $prev]) }}" class="flex h-10 w-10 items-center justify-center rounded-xl border border-line bg-surface text-white hover:border-line hover:text-accent transition-colors">
                        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M15 6l-6 6 6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                    <form method="GET" action="{{ route('football.matchday') }}">
                        <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()"
                               class="rounded-xl border border-line bg-ink px-3 py-2 text-sm font-bold font-mono text-white focus:outline-none focus:ring-2 focus:ring-line">
                    </form>
                    <a href="{{ route('football.matchday', ['date' => $next]) }}" class="flex h-10 w-10 items-center justify-center rounded-xl border border-line bg-surface text-white hover:border-line hover:text-accent transition-colors">
                        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M9 6l6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                </div>
            </div>
        </div>

        @if(count($fixtures) > 0)
            @foreach($byLeague as $leagueName => $leagueFixtures)
                <div class="space-y-3">
                    <h2 class="flex items-center gap-2 text-sm font-bold text-white">
                        @if(!empty($leagueFixtures[0]['league']['image_path']))
                            <img src="{{ $leagueFixtures[0]['league']['image_path'] }}" alt="" class="h-5 w-5 object-contain">
                        @endif
                        {{ $leagueName }}
                        <span class="text-xs font-mono font-bold text-white0">({{ count($leagueFixtures) }})</span>
                        @if($enabledIds->contains((int) ($leagueFixtures[0]['league_id'] ?? $leagueFixtures[0]['league']['id'] ?? 0)))
                            <span class="rounded-lg border border-line px-1.5 py-0.5 text-xs font-bold uppercase tracking-wider text-accent">{{ __('football.matchday.main_league') }}</span>
                        @endif
                    </h2>
                    <div class="grid gap-4 md:grid-cols-2">
                        @foreach($leagueFixtures as $f)
                            @include('football.partials.live-card', ['f' => $f])
                        @endforeach
                    </div>
                </div>
            @endforeach
        @else
            <div class="rounded-xl border border-dashed border-line bg-surface p-12 text-center">
                <p class="text-base font-bold text-white">{{ __('football.matchday.empty') }}</p>
                <p class="text-xs text-white0 mt-1">{{ __('football.matchday.empty_hint') }}</p>
            </div>
        @endif
    </div>
@endsection
