@extends('layouts.app')

@section('title', __('football.player.title', ['name' => $player['display_name'] ?? $player['name'] ?? __('football.player.fallback_title')]))

@section('content')
<div class="space-y-8">

    {{-- Breadcrumb & Back --}}
    <div class="flex items-center justify-between">
        <a href="{{ url()->previous() ?? route('football.index') }}" class="text-xs font-bold text-body hover:text-accent transition-colors flex items-center gap-1.5 bg-surface border border-line px-3.5 py-2 rounded-xl">
            {{ __('football.player.back') }}
        </a>
        <div class="flex items-center gap-3">
            <a href="{{ route('football.compare', ['p1' => $player['id'] ?? 0]) }}"
               class="inline-flex items-center gap-1.5 rounded-xl border border-line bg-surface px-3 py-2 text-xs font-bold text-white hover:text-accent hover:border-line transition-colors">
                <svg viewBox="0 0 24 24" fill="none" class="w-3.5 h-3.5"><path d="M8 3v4M16 17v4M4 5h8M12 19h8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                {{ __('football.player.compare') }}
            </a>
            <span class="text-xs text-white0 font-mono font-bold">ID: #{{ $player['id'] ?? '-' }}</span>
        </div>
    </div>

    {{-- PLAYER HERO BANNER --}}
    <div class="bg-surface rounded-xl p-6 sm:p-10 border border-line relative overflow-hidden">
        {{-- Background Glow --}}

        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-8 relative z-10">
            {{-- Player Photo --}}
            <div class="w-32 h-32 sm:w-40 sm:h-40 rounded-xl bg-ink border-2 border-line p-1 flex-shrink-0 overflow-hidden flex items-center justify-center">
                @if(!empty($player['image_path']))
                    <img src="{{ $player['image_path'] }}" alt="{{ $player['name'] }}" class="w-full h-full object-cover rounded-xl">
                @else
                    <span class="text-6xl"><x-icon name="user" class="h-4 w-4" /></span>
                @endif
            </div>

            {{-- Player Info --}}
            @php
                // Single source for citizenship + flag so the badge and the metric
                // box never disagree. Nationality (national team) takes priority,
                // country of birth is the fallback.
                $natCountry = $nationality ?: $country;
            @endphp
            <div class="flex-1 text-center sm:text-left space-y-4">
                <div>
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mb-2">
                        @if(!empty($position) || !empty($player['position_id']))
                            <span class="px-3 py-1 rounded-lg text-xs font-bold text-accent border border-line uppercase tracking-wider">
                                {{ $position ?: __('football.player.position_fallback', ['id' => $player['position_id']]) }}
                            </span>
                        @endif
                        @if(!empty($detailedPosition) && $detailedPosition !== $position)
                            <span class="px-3 py-1 rounded-lg text-xs font-bold bg-surface text-white border border-line">
                                {{ $detailedPosition }}
                            </span>
                        @endif
                        @if($natCountry)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-surface text-white border border-line">
                                @if(!empty($natCountry['image_path']))
                                    <img src="{{ $natCountry['image_path'] }}" alt="{{ $natCountry['name'] }}" class="w-4 h-4 rounded-sm object-cover">
                                @else
                                    <x-icon name="globe" class="h-4 w-4" />
                                @endif
                                {{ $natCountry['name'] }}
                            </span>
                        @endif
                    </div>
                    <h1 class="text-2xl sm:text-4xl font-bold tracking-tight text-white">
                        {{ $player['display_name'] ?? $player['name'] ?? __('football.player.name_fallback') }}
                    </h1>
                    @if(!empty($player['common_name']) && $player['common_name'] !== $player['name'])
                        <p class="text-sm text-body font-medium mt-1">{{ __('football.player.full_name', ['name' => $player['name']]) }}</p>
                    @endif
                </div>

                {{-- Player Metrics Grid --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2">
                    <div class="bg-ink border border-line p-3 rounded-xl">
                        <span class="text-xs uppercase font-bold text-white0 block">{{ __('football.player.nationality') }}</span>
                        <span class="flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-white">
                            @if(!empty($natCountry['image_path']))
                                <img src="{{ $natCountry['image_path'] }}" alt="" class="w-4 h-4 rounded-sm object-cover">
                            @endif
                            {{ $natCountry['name'] ?? '-' }}
                        </span>
                    </div>
                    <div class="bg-ink border border-line p-3 rounded-xl">
                        <span class="text-xs uppercase font-bold text-white0 block">{{ __('football.player.height') }}</span>
                        <span class="text-xs sm:text-sm font-semibold text-white font-mono">{{ $player['height'] ? $player['height'] . ' cm' : '-' }}</span>
                    </div>
                    <div class="bg-ink border border-line p-3 rounded-xl">
                        <span class="text-xs uppercase font-bold text-white0 block">{{ __('football.player.weight') }}</span>
                        <span class="text-xs sm:text-sm font-semibold text-white font-mono">{{ $player['weight'] ? $player['weight'] . ' kg' : '-' }}</span>
                    </div>
                    <div class="bg-ink border border-line p-3 rounded-xl">
                        <span class="text-xs uppercase font-bold text-white0 block">{{ __('football.player.date_of_birth') }}</span>
                        <span class="text-xs sm:text-sm font-semibold text-white font-mono">
                            {{ $player['date_of_birth'] ? \Illuminate\Support\Carbon::parse($player['date_of_birth'])->locale(app()->getLocale())->translatedFormat('d M Y') : '-' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- PLAYER DETAILS CONTENT GRID --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

        {{-- CLUB HISTORY (per musim, dari squad membership) --}}
        <div class="bg-surface border border-line rounded-xl p-6 space-y-4">
            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl border border-line text-accent">
                    <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5"><path d="M12 3l7 3v5c0 4-3 6.5-7 8-4-1.5-7-4-7-8V6l7-3z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
                </span>
                <div>
                    <span class="kicker block text-xs font-bold uppercase text-primary">{{ __('football.player.career_kicker') }}</span>
                    <h3 class="text-base font-bold text-white">{{ __('football.player.career_heading') }}</h3>
                </div>
            </div>

            @if(!empty($clubHistory))
                <div class="space-y-2.5">
                    @foreach($clubHistory as $ch)
                        <a href="{{ route('football.team', $ch['team_id']) }}?season_id={{ $ch['season_id'] }}"
                           class="group flex items-center gap-3.5 bg-ink p-3.5 rounded-xl border {{ !empty($ch['is_current']) ? 'border-line' : 'border-line' }} hover:border-line transition-all">
                            @if(!empty($ch['team']['image_path']))
                                <img src="{{ $ch['team']['image_path'] }}" alt="" class="w-11 h-11 object-contain shrink-0">
                            @else
                                <div class="w-11 h-11 rounded-xl bg-surface flex items-center justify-center text-sm shrink-0"><x-icon name="shield" class="h-4 w-4" /></div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-bold text-sm text-white group-hover:text-accent transition-colors truncate">{{ $ch['team']['name'] ?? __('football.player.club_fallback', ['id' => $ch['team_id']]) }}</h4>
                                    @if(!empty($ch['is_current']))
                                        <span class="text-xs font-bold uppercase tracking-wider text-accent px-1.5 py-0.5 rounded-lg border border-line">{{ __('football.player.current') }}</span>
                                    @endif
                                    @if(!empty($ch['captain']))
                                        <span title="{{ __('football.player.captain') }}" class="flex h-4 w-4 items-center justify-center rounded-lg bg-gold text-muted text-xs font-bold">C</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 text-xs text-white0 mt-0.5">
                                    <span class="font-mono">{{ __('football.player.season_prefix', ['name' => $ch['season_name'] ?: '#'.$ch['season_id']]) }}</span>
                                    @if(!empty($ch['jersey_number']))
                                        <span class="text-muted">•</span>
                                        <span class="font-mono font-bold text-body">No. {{ $ch['jersey_number'] }}</span>
                                    @endif
                                </div>
                            </div>
                            <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 text-muted group-hover:text-accent transition-colors shrink-0"><path d="M9 6l6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </a>
                    @endforeach
                </div>
                <p class="text-xs text-white0">{{ __('football.player.career_note') }}</p>
            @else
                <p class="text-body text-xs py-4">{{ __('football.player.career_empty') }}</p>
            @endif
        </div>

        {{-- TOPSCORER / METRIC RECORDS --}}
        <div class="bg-surface border border-line rounded-xl p-6 space-y-4">
            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl border border-line text-gold">
                    <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5"><path d="M8 21h8M12 17v4M7 4h10v4a5 5 0 01-10 0V4zM7 6H4v1a3 3 0 003 3M17 6h3v1a3 3 0 01-3 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <div>
                    <span class="kicker block text-xs font-bold uppercase text-primary">{{ __('football.player.records_kicker') }}</span>
                    <h3 class="text-base font-bold text-white">{{ __('football.player.records_heading') }}</h3>
                </div>
            </div>

            @php
                // Group records per season (season heading, then metric rows).
                $tsBySeason = collect($topscorers)->groupBy(fn ($ts) => $ts['season_name'] ?: ('#' . ($ts['season_id'] ?? '-')));
            @endphp
            @if($tsBySeason->count() > 0)
                <div class="space-y-4">
                    @foreach($tsBySeason as $seasonName => $rows)
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-white font-mono">{{ __('football.player.season_prefix', ['name' => $seasonName]) }}</span>
                                <span class="h-px flex-1 bg-surface"></span>
                                @if(!empty($rows[0]['team']))
                                    <span class="flex items-center gap-1.5 text-xs text-white0">
                                        @if(!empty($rows[0]['team']['image_path']))
                                            <img src="{{ $rows[0]['team']['image_path'] }}" alt="" class="w-3.5 h-3.5 object-contain">
                                        @endif
                                        {{ $rows[0]['team']['name'] }}
                                    </span>
                                @endif
                            </div>
                            @foreach($rows as $ts)
                                @php
                                    $cat = strtolower($ts['type_name'] ?? '');
                                    $catIcon = str_contains($cat, 'assist') ? \App\Support\Icon::svg('boot', 'h-3.5 w-3.5') : (str_contains($cat, 'yellow') || str_contains($cat, 'kuning') ? \App\Support\Icon::svg('card-yellow', 'h-3.5 w-3.5') : (str_contains($cat, 'red') || str_contains($cat, 'merah') ? \App\Support\Icon::svg('card-red', 'h-3.5 w-3.5') : \App\Support\Icon::svg('ball', 'h-3.5 w-3.5')));
                                @endphp
                                <div class="bg-ink px-3.5 py-2.5 rounded-xl border border-line flex items-center justify-between gap-3">
                                    <span class="flex items-center gap-1.5 text-xs font-semibold text-white">{!! $catIcon !!} {{ $ts['type_name'] ?: __('football.player.goals_fallback') }}</span>
                                    <div class="flex items-center gap-3 shrink-0">
                                        <span class="text-xs font-semibold text-body">{{ __('football.player.rank') }} <strong class="text-white font-mono">#{{ $ts['position'] }}</strong></span>
                                        <span class="px-3 py-1 rounded-lg text-accent font-mono font-bold text-xs border border-line">
                                            {{ $ts['total'] }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-body text-xs py-4">{{ __('football.player.records_empty') }}</p>
            @endif
        </div>

        {{-- SEASON STATISTICS (from player_statistics table) --}}
        @if(!empty($statistics))
            <div class="lg:col-span-2 bg-surface border border-line rounded-xl p-6 space-y-4">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl border border-line text-accent">
                        <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5"><path d="M5 19V9m4.5 10V5m4.5 14v-7m4.5 7V8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    </span>
                    <div>
                        <span class="kicker block text-xs font-bold uppercase text-primary">{{ __('football.player.stats_kicker') }}</span>
                        <h3 class="text-base font-bold text-white">{{ __('football.player.stats_heading') }}</h3>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-sm whitespace-nowrap">
                        <thead class="bg-ink text-body font-bold uppercase tracking-wider border-b border-line text-xs">
                            <tr>
                                <th class="py-3 px-3">{{ __('football.player.th.season') }}</th>
                                <th class="py-3 px-3">{{ __('football.player.th.club') }}</th>
                                <th class="py-3 px-2 text-center" title="{{ __('football.player.th.apps_title') }}">{{ __('football.player.th.apps') }}</th>
                                <th class="py-3 px-2 text-center" title="{{ __('football.player.th.minutes_title') }}">{{ __('football.player.th.minutes') }}</th>
                                <th class="py-3 px-2 text-center text-accent" title="{{ __('football.player.th.goals_title') }}"><x-icon name="ball" class="h-4 w-4" /></th>
                                <th class="py-3 px-2 text-center text-steel" title="{{ __('football.player.th.assists_title') }}"><x-icon name="boot" class="h-4 w-4" /></th>
                                <th class="py-3 px-2 text-center" title="{{ __('football.player.th.yellow_title') }}"><x-icon name="card-yellow" class="h-4 w-4" /></th>
                                <th class="py-3 px-2 text-center" title="{{ __('football.player.th.red_title') }}"><x-icon name="card-red" class="h-4 w-4" /></th>
                                <th class="py-3 px-2 text-center text-gold" title="{{ __('football.player.th.rating_title') }}">{{ __('football.player.th.rating') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line font-medium text-white">
                            @foreach($statistics as $s)
                                <tr class="hover:bg-surface transition-colors">
                                    <td class="py-3 px-3 font-bold text-white">{{ $s['season_name'] ?? ('#'.($s['season_id'] ?? '-')) }}</td>
                                    <td class="py-3 px-3">
                                        @if(!empty($s['team']))
                                            <span class="flex items-center gap-2">
                                                @if(!empty($s['team']['image_path']))
                                                    <img src="{{ $s['team']['image_path'] }}" alt="" class="w-4 h-4 object-contain">
                                                @endif
                                                <span class="truncate max-w-[140px]">{{ $s['team']['name'] }}</span>
                                            </span>
                                        @else
                                            <span class="text-white0">—</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-2 text-center font-mono">{{ $s['appearances'] ?? 0 }}</td>
                                    <td class="py-3 px-2 text-center font-mono text-body">{{ $s['minutes'] ?? 0 }}'</td>
                                    <td class="py-3 px-2 text-center font-mono font-bold text-accent">{{ $s['goals'] ?? 0 }}</td>
                                    <td class="py-3 px-2 text-center font-mono font-bold text-steel">{{ $s['assists'] ?? 0 }}</td>
                                    <td class="py-3 px-2 text-center font-mono text-gold">{{ $s['yellow_cards'] ?? 0 }}</td>
                                    <td class="py-3 px-2 text-center font-mono text-accent">{{ $s['red_cards'] ?? 0 }}</td>
                                    <td class="py-3 px-2 text-center">
                                        @if(!empty($s['rating']))
                                            @php $rt = (float) $s['rating']; $rtCls = $rt >= 7 ? 'text-accent' : ($rt >= 6 ? 'text-gold' : 'text-accent'); @endphp
                                            <span class="font-mono font-bold {{ $rtCls }}">{{ number_format($rt, 2) }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- TRANSFER HISTORY TIMELINE --}}
        @if(count($transfers) > 0)
            <div class="lg:col-span-2 bg-surface border border-line rounded-xl p-6 space-y-4">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    {{ __('football.player.transfers_heading') }}
                </h3>

                <div class="space-y-3">
                    @foreach($transfers as $tr)
                        @php
                            // Compact transfer fee (euros): 45000000 -> €45.0M, 850000 -> €850K
                            $amount = $tr['amount'] ?? null;
                            $feeText = null;
                            if (!empty($amount) && $amount > 0) {
                                if ($amount >= 1000000) {
                                    $feeText = '€' . rtrim(rtrim(number_format($amount / 1000000, 1), '0'), '.') . 'M';
                                } elseif ($amount >= 1000) {
                                    $feeText = '€' . round($amount / 1000) . 'K';
                                } else {
                                    $feeText = '€' . number_format($amount);
                                }
                            }
                            $typeLabel = $tr['type_name'] ?? ($tr['type'] ?? 'Transfer');
                        @endphp
                        <div class="bg-ink p-4 rounded-xl border border-line flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-accent">{{ $typeLabel }}</span>
                                    @if($feeText)
                                        <span class="text-xs font-mono font-bold px-2 py-0.5 rounded-lg text-gold border border-line">{{ $feeText }}</span>
                                    @endif
                                </div>
                                <span class="text-xs text-body font-medium">{{ $tr['date'] ? \Illuminate\Support\Carbon::parse($tr['date'])->locale(app()->getLocale())->translatedFormat('d F Y') : __('football.transfers.official') }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-xs font-bold">
                                <span class="flex items-center gap-1.5 bg-surface px-2.5 py-1.5 rounded-xl text-white border border-line">
                                    @if(!empty($tr['from_team']['image_path']))
                                        <img src="{{ $tr['from_team']['image_path'] }}" alt="" class="w-4 h-4 object-contain">
                                    @endif
                                    <span class="truncate max-w-[120px]">{{ $tr['from_team']['name'] ?? __('football.transfers.from_club') }}</span>
                                </span>
                                <span class="text-accent font-mono font-bold">&rarr;</span>
                                <span class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl text-accent border border-line">
                                    @if(!empty($tr['to_team']['image_path']))
                                        <img src="{{ $tr['to_team']['image_path'] }}" alt="" class="w-4 h-4 object-contain">
                                    @endif
                                    <span class="truncate max-w-[120px]">{{ $tr['to_team']['name'] ?? __('football.transfers.to_club') }}</span>
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>

</div>
@endsection
