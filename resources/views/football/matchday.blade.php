@extends('layouts.app')

@section('title', 'Jadwal Pertandingan - KREASIBALL')

@php
    $today = date('Y-m-d');
    $prev = date('Y-m-d', strtotime($date.' -1 day'));
    $next = date('Y-m-d', strtotime($date.' +1 day'));
    // Group fixtures by league name for readability
    $byLeague = collect($fixtures)->groupBy(fn ($f) => $f['league']['name'] ?? 'Lainnya');
@endphp

@section('content')
    <div class="space-y-8">
        {{-- Header + date navigator --}}
        <div class="pitch-stripes bg-gradient-to-r from-slate-900 via-slate-900/90 to-slate-950 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl relative overflow-hidden">
            <div class="absolute -right-16 -top-16 w-72 h-72 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="relative z-10 flex flex-col sm:flex-row sm:items-end justify-between gap-6">
                <div>
                    <span class="kicker inline-block text-[10px] font-bold uppercase text-emerald-400 mb-3">MATCHDAY</span>
                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
                        {{ $date === $today ? 'Pertandingan Hari Ini' : \Illuminate\Support\Carbon::parse($date)->translatedFormat('l, d F Y') }}
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-400 mt-1.5 font-mono">{{ count($fixtures) }} pertandingan terjadwal.</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('football.matchday', ['date' => $prev]) }}" class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-800 bg-slate-900 text-slate-300 hover:border-slate-700 hover:text-emerald-400 transition-colors">
                        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M15 6l-6 6 6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                    <form method="GET" action="{{ route('football.matchday') }}">
                        <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()"
                               class="rounded-xl border border-slate-800 bg-slate-950 px-3 py-2 text-sm font-bold font-mono text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </form>
                    <a href="{{ route('football.matchday', ['date' => $next]) }}" class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-800 bg-slate-900 text-slate-300 hover:border-slate-700 hover:text-emerald-400 transition-colors">
                        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M9 6l6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                </div>
            </div>
        </div>

        @if(count($fixtures) > 0)
            @foreach($byLeague as $leagueName => $leagueFixtures)
                <div class="space-y-3">
                    <h2 class="flex items-center gap-2 text-sm font-black text-slate-300">
                        @if(!empty($leagueFixtures[0]['league']['image_path']))
                            <img src="{{ $leagueFixtures[0]['league']['image_path'] }}" alt="" class="h-5 w-5 object-contain">
                        @endif
                        {{ $leagueName }}
                        <span class="text-xs font-mono font-bold text-slate-500">({{ count($leagueFixtures) }})</span>
                    </h2>
                    <div class="grid gap-4 md:grid-cols-2">
                        @foreach($leagueFixtures as $f)
                            @include('football.partials.live-card', ['f' => $f])
                        @endforeach
                    </div>
                </div>
            @endforeach
        @else
            <div class="rounded-3xl border border-dashed border-slate-800 bg-slate-900/40 p-12 text-center">
                <p class="text-base font-bold text-slate-200">Tidak ada pertandingan pada tanggal ini.</p>
                <p class="text-xs text-slate-500 mt-1">Coba tanggal lain dengan navigasi di atas.</p>
            </div>
        @endif
    </div>
@endsection
