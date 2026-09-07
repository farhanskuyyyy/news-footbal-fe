@extends('layouts.app')

@section('title', __('football.team.title', ['name' => $team['name'] ?? __('football.team.fallback')]))

@section('content')
<div class="space-y-8">

    {{-- Breadcrumb --}}
    <div>
        <a href="{{ url()->previous() ?? route('football.index') }}" class="text-xs font-bold text-body hover:text-accent transition-colors flex items-center gap-1.5 bg-surface border border-line px-3.5 py-2 rounded-xl inline-flex">
            {{ __('football.team.back') }}
        </a>
    </div>

    {{-- Team Header Card --}}
    <div class="bg-surface rounded-xl border border-line p-6 sm:p-10 relative overflow-hidden">
        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-8 relative z-10">
            <div class="w-28 h-28 p-4 bg-ink rounded-xl border border-line flex items-center justify-center flex-shrink-0">
                @if(!empty($team['image_path']))
                    <img src="{{ $team['image_path'] }}" alt="{{ $team['name'] }}" class="max-h-full max-w-full object-contain drop-">
                @else
                    <span class="text-5xl"><x-icon name="shield" class="h-4 w-4" /></span>
                @endif
            </div>

            <div class="flex-1 text-center sm:text-left space-y-3">
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-3">
                    <h1 class="text-2xl sm:text-4xl font-bold text-white tracking-tight">{{ $team['name'] }}</h1>
                    @if(!empty($team['short_code']))
                        <span class="px-3 py-1 rounded-lg text-xs font-mono font-bold text-accent border border-line uppercase tracking-widest">
                            {{ $team['short_code'] }}
                        </span>
                    @endif
                </div>

                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-5 text-xs text-body font-medium pt-1">
                    @if(!empty($team['founded']))
                        <span class="flex items-center gap-1.5"><x-icon name="calendar" class="h-4 w-4" /> {{ __('football.team.founded') }} <strong class="text-white">{{ $team['founded'] }}</strong></span>
                    @endif
                    @if($venue)
                        <span class="flex items-center gap-1.5"><x-icon name="location" class="h-4 w-4" /> {{ __('football.team.stadium') }} <strong class="text-white">{{ $venue['name'] }}</strong> ({{ $venue['city_name'] ?? '' }})</span>
                        @if(!empty($venue['capacity']))
                            <span class="flex items-center gap-1.5"><x-icon name="users" class="h-4 w-4" /> {{ __('football.team.capacity') }} <strong class="text-white">{{ number_format($venue['capacity']) }}</strong></span>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Coach + Rivals row --}}
    @if($coach || !empty($rivals))
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            {{-- Head Coach card --}}
            @if($coach)
                <div class="bg-surface border border-line rounded-xl p-5 flex items-center gap-4">
                    @if(!empty($coach['image_path']))
                        <img src="{{ $coach['image_path'] }}" alt="{{ $coach['name'] ?? '' }}" class="h-16 w-16 rounded-xl object-cover border-2 border-line bg-ink">
                    @else
                        <div class="flex h-16 w-16 items-center justify-center rounded-xl bg-surface text-2xl border border-line"><x-icon name="coach" class="h-4 w-4" /></div>
                    @endif
                    <div class="min-w-0">
                        <span class="kicker block text-xs font-bold uppercase text-primary">{{ __('football.team.coach_kicker') }}</span>
                        <h3 class="text-base font-bold text-white truncate mt-0.5">{{ $coach['display_name'] ?? $coach['name'] ?? __('football.team.coach_fallback') }}</h3>
                        @if(!empty($coach['date_of_birth']))
                            <p class="text-xs text-white0 font-mono">{{ date_diff(date_create($coach['date_of_birth']), date_create('today'))->y }} {{ __('football.team.years_short') }}</p>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Rivals chips --}}
            @if(!empty($rivals))
                <div class="bg-surface border border-line rounded-xl p-5 {{ $coach ? 'lg:col-span-2' : 'lg:col-span-3' }}">
                    <span class="kicker block text-xs font-bold uppercase text-primary mb-3">{{ __('football.team.rivals') }}</span>
                    <div class="flex flex-wrap gap-2">
                        @foreach($rivals as $rv)
                            <a href="{{ route('football.team', $rv['id']) }}?season_id={{ $seasonId }}"
                               class="group flex items-center gap-2 rounded-lg border border-line bg-ink pl-1.5 pr-3.5 py-1.5 hover:border-line transition-colors">
                                @if(!empty($rv['image_path']))
                                    <img src="{{ $rv['image_path'] }}" alt="" class="h-6 w-6 object-contain">
                                @else
                                    <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-surface text-xs"><x-icon name="shield" class="h-4 w-4" /></span>
                                @endif
                                <span class="text-xs font-bold text-white group-hover:text-white transition-colors">{{ $rv['name'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- Stadium / Venue card --}}
    @if($venue)
        <div class="bg-surface border border-line rounded-xl overflow-hidden flex flex-col sm:flex-row">
            @if(!empty($venue['image_path']))
                <div class="relative sm:w-1/3 h-40 sm:h-auto bg-ink">
                    <img src="{{ $venue['image_path'] }}" alt="{{ $venue['name'] ?? '' }}" class="h-full w-full object-cover" loading="lazy" onerror="this.style.display='none'">
                </div>
            @endif
            <div class="flex-1 p-6">
                <span class="kicker block text-xs font-bold uppercase text-primary mb-2">{{ __('football.team.venue_kicker') }}</span>
                <h3 class="text-lg font-bold text-white">{{ $venue['name'] ?? '-' }}</h3>
                @if(!empty($venue['address']))
                    <p class="text-xs text-body mt-1">{{ $venue['address'] }}</p>
                @endif
                <div class="mt-4 grid grid-cols-3 gap-3">
                    <div class="rounded-xl border border-line bg-ink p-3">
                        <span class="block text-xs uppercase font-bold text-white0">{{ __('football.team.city') }}</span>
                        <span class="text-sm font-semibold text-white truncate block">{{ $venue['city_name'] ?? '-' }}</span>
                    </div>
                    <div class="rounded-xl border border-line bg-ink p-3">
                        <span class="block text-xs uppercase font-bold text-white0">{{ __('football.team.capacity_label') }}</span>
                        <span class="text-sm font-semibold text-white font-mono block">{{ !empty($venue['capacity']) ? number_format($venue['capacity']) : '-' }}</span>
                    </div>
                    <div class="rounded-xl border border-line bg-ink p-3">
                        <span class="block text-xs uppercase font-bold text-white0">{{ __('football.team.surface') }}</span>
                        <span class="text-sm font-semibold text-white capitalize truncate block">{{ $venue['surface'] ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Recent results + form (live from Sportmonks proxy) --}}
    @if(!empty($recent))
        @php
            $tid = $team['id'] ?? 0;
            $formArr = [];
            foreach ($recent as $f) {
                $loc = null;
                foreach (($f['participants'] ?? []) as $p) {
                    if (($p['id'] ?? 0) == $tid) { $loc = $p['meta']['location'] ?? null; break; }
                }
                if (! $loc) continue;
                $mine = null; $opp = null;
                foreach (($f['scores'] ?? []) as $s) {
                    if (($s['description'] ?? '') === 'CURRENT') {
                        $pp = $s['score']['participant'] ?? '';
                        $g = $s['score']['goals'] ?? null;
                        if ($pp === $loc) $mine = $g; else $opp = $g;
                    }
                }
                if ($mine === null || $opp === null) continue;
                $formArr[] = $mine > $opp ? 'W' : ($mine < $opp ? 'L' : 'D');
            }
            $formStrip = array_reverse($formArr); // oldest <x-icon name="arrow-right" class="h-4 w-4" /> newest
        @endphp

        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl border border-line text-accent">
                        <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5"><path d="M4 7h16M4 12h10M4 17h7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    </span>
                    <div>
                        <span class="kicker block text-xs font-bold uppercase text-primary">{{ __('football.team.performance') }}</span>
                        <h2 class="text-lg font-bold text-white">{{ __('football.team.recent_results') }}</h2>
                    </div>
                </div>
                @if(!empty($formStrip))
                    <div class="flex items-center gap-1">
                        @foreach($formStrip as $r)
                            @php $cls = match($r) { 'W' => 'bg-primary text-white', 'D' => 'bg-line text-white', 'L' => 'bg-accent text-white', default => 'bg-surface' }; @endphp
                            <span class="flex h-6 w-6 items-center justify-center rounded-lg text-xs font-bold font-mono {{ $cls }}">{{ $r }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                @foreach($recent as $f)
                    @include('football.partials.live-card', ['f' => $f])
                @endforeach
            </div>
        </div>
    @endif

    {{-- Upcoming fixtures (live from Sportmonks proxy) --}}
    @if(!empty($upcoming))
        <div class="space-y-3">
            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl border border-line text-accent">
                    <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5"><rect x="4" y="5.5" width="16" height="14" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M4 9.5h16M8 3.5v4M16 3.5v4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                </span>
                <div>
                    <span class="kicker block text-xs font-bold uppercase text-primary">{{ __('football.team.schedule') }}</span>
                    <h2 class="text-lg font-bold text-white">{{ __('football.team.next_matches') }}</h2>
                </div>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                @foreach($upcoming as $f)
                    @include('football.partials.live-card', ['f' => $f])
                @endforeach
            </div>
        </div>
    @endif

    {{-- Squad / Players Section --}}
    <div class="bg-surface border border-line rounded-xl p-6 sm:p-8 space-y-6">
        <div class="flex items-center justify-between pb-4 border-b border-line">
            <div>
                <h2 class="text-lg font-bold text-white flex items-center gap-2">{{ __('football.team.squad_heading') }}</h2>
                <p class="text-xs text-body mt-0.5">{{ __('football.team.squad_sub') }}</p>
            </div>
            <span class="text-xs font-mono font-bold px-3.5 py-1.5 bg-ink text-accent border border-line rounded-xl">
                {{ trans_choice('football.team.players_count', count($players), ['count' => count($players)]) }}
            </span>
        </div>

        @php
            // Map player_id -> squad row (jersey number, captain flag) from the squads payload
            $squadByPlayer = [];
            foreach (($squads ?? []) as $sq) {
                if (!empty($sq['player_id'])) {
                    $squadByPlayer[$sq['player_id']] = $sq;
                }
            }
            // Sort players by jersey number (unnumbered last)
            $sortedPlayers = collect($players)->sortBy(function ($p) use ($squadByPlayer) {
                return $squadByPlayer[$p['id']]['jersey_number'] ?? 999;
            })->values()->all();
        @endphp

        @if(count($players) > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($sortedPlayers as $p)
                    @php
                        $sq = $squadByPlayer[$p['id']] ?? null;
                        $jersey = $sq['jersey_number'] ?? null;
                        $isCaptain = !empty($sq['captain']);
                    @endphp
                    <a href="{{ route('football.player', $p['id']) }}" class="group relative p-4 rounded-xl border border-line bg-ink hover:bg-surface hover:border-line hover: transition-all flex items-center gap-3.5">
                        @if($isCaptain)
                            <span title="{{ __('football.team.captain') }}" class="absolute top-2.5 right-2.5 flex h-5 w-5 items-center justify-center rounded-lg bg-gold text-muted text-xs font-bold shadow">C</span>
                        @endif

                        <div class="relative flex-shrink-0">
                            @if(!empty($p['image_path']))
                                <img src="{{ $p['image_path'] }}" alt="{{ $p['name'] }}" class="w-12 h-12 rounded-lg object-cover border-2 border-line bg-surface group-hover:border-line transition-colors">
                            @else
                                <div class="w-12 h-12 rounded-lg bg-surface text-white flex items-center justify-center font-bold text-sm border border-line">
                                    <x-icon name="user" class="h-4 w-4" />
                                </div>
                            @endif
                            @if($jersey !== null)
                                <span class="absolute -bottom-1 -left-1 flex h-5 min-w-[20px] px-1 items-center justify-center rounded-lg text-white text-xs font-bold font-mono border border-line shadow">{{ $jersey }}</span>
                            @endif
                        </div>

                        <div class="min-w-0 flex-1">
                            <h4 class="font-semibold text-white text-sm truncate group-hover:text-accent transition-colors">{{ $p['display_name'] ?? $p['name'] }}</h4>
                            <p class="mt-0.5 flex items-center gap-1.5 truncate text-xs font-medium text-body">
                                @php
                                    $pid = $p['position_id'] ?? 0;
                                    $posIcon = match($pid) { 24 => \App\Support\Icon::svg('gloves', 'h-3.5 w-3.5'), 25 => \App\Support\Icon::svg('shield', 'h-3.5 w-3.5'), 26 => \App\Support\Icon::svg('gear', 'h-3.5 w-3.5'), 27 => \App\Support\Icon::svg('bolt', 'h-3.5 w-3.5'), default => \App\Support\Icon::svg('ball', 'h-3.5 w-3.5') };
                                    // Prefer the resolved Type-dictionary name; fall back to translated labels
                                    $posLabel = ($positions[$pid] ?? null) ?: match($pid) {
                                        24 => __('football.team.positions.gk'),
                                        25 => __('football.team.positions.def'),
                                        26 => __('football.team.positions.mid'),
                                        27 => __('football.team.positions.fwd'),
                                        default => __('football.team.positions.player'),
                                    };
                                @endphp
                                {!! $posIcon !!} {{ $posLabel }}
                                @if(!empty($p['date_of_birth']))
                                    • {{ date_diff(date_create($p['date_of_birth']), date_create('today'))->y }} {{ __('football.team.years_short') }}
                                @endif
                            </p>
                            @if(!empty($p['height']))
                                <span class="text-xs text-white0 font-mono block">{{ $p['height'] }} cm / {{ $p['weight'] ?? '-' }} kg</span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="p-12 text-center text-body text-sm">
                {{ __('football.team.players_empty') }}
            </div>
        @endif
    </div>

</div>
@endsection
