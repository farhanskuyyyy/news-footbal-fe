@extends('layouts.app')

@section('title', __('football.compare.title'))

@php
    // Aggregate a player's season statistics into career totals + avg rating.
    $agg = function ($detail) {
        $out = ['goals' => 0, 'assists' => 0, 'apps' => 0, 'minutes' => 0, 'yellow' => 0, 'red' => 0, 'rating' => null];
        $rSum = 0; $rN = 0;
        foreach (($detail['statistics'] ?? []) as $s) {
            $out['goals'] += (int) ($s['goals'] ?? 0);
            $out['assists'] += (int) ($s['assists'] ?? 0);
            $out['apps'] += (int) ($s['appearances'] ?? 0);
            $out['minutes'] += (int) ($s['minutes'] ?? 0);
            $out['yellow'] += (int) ($s['yellow_cards'] ?? 0);
            $out['red'] += (int) ($s['red_cards'] ?? 0);
            if (!empty($s['rating'])) { $rSum += (float) $s['rating']; $rN++; }
        }
        if ($rN > 0) $out['rating'] = round($rSum / $rN, 2);
        return $out;
    };
    $a1 = $player1 ? $agg($player1) : null;
    $a2 = $player2 ? $agg($player2) : null;

    // metrics to compare: label, key, higher-is-better
    $metrics = [
        [__('football.compare.metrics.apps'), 'apps', true],
        [__('football.compare.metrics.goals'), 'goals', true],
        [__('football.compare.metrics.assists'), 'assists', true],
        [__('football.compare.metrics.minutes'), 'minutes', true],
        [__('football.compare.metrics.yellow'), 'yellow', false],
        [__('football.compare.metrics.red'), 'red', false],
        [__('football.compare.metrics.rating'), 'rating', true],
    ];
@endphp

@section('content')
    <div class="space-y-8">
        <div class="flex items-center gap-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl border border-line text-accent">
                <svg viewBox="0 0 24 24" fill="none" class="h-6 w-6"><path d="M8 3v4M16 17v4M4 5h8M12 19h8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><circle cx="16" cy="5" r="2.5" stroke="currentColor" stroke-width="1.6"/><circle cx="8" cy="19" r="2.5" stroke="currentColor" stroke-width="1.6"/></svg>
            </span>
            <div>
                <span class="kicker block text-xs font-bold uppercase text-primary">{{ __('football.compare.kicker') }}</span>
                <h1 class="text-2xl font-bold text-white">{{ __('football.compare.heading') }}</h1>
            </div>
        </div>

        {{-- Pickers --}}
        <form method="GET" action="{{ route('football.compare') }}" class="grid grid-cols-1 sm:grid-cols-[1fr_auto_1fr] gap-3 items-center">
            <input type="number" name="p1" value="{{ $p1id }}" placeholder="Player ID 1" class="rounded-xl border border-line bg-ink px-3.5 py-2.5 text-sm font-mono text-white placeholder:text-white focus:outline-none focus:ring-2 focus:ring-line">
            <span class="text-center text-xs font-bold text-white">VS</span>
            <input type="number" name="p2" value="{{ $p2id }}" placeholder="Player ID 2" class="rounded-xl border border-line bg-ink px-3.5 py-2.5 text-sm font-mono text-white placeholder:text-white focus:outline-none focus:ring-2 focus:ring-line">
            <div class="sm:col-span-3 flex justify-center">
                <button class="rounded-xl bg-primary hover:bg-accent px-5 py-2 text-sm font-bold text-white transition-colors">{{ __('football.compare.submit') }}</button>
            </div>
        </form>
        <p class="text-center text-xs text-muted">{!! __('football.compare.hint', ['link' => '<a href="'.route('football.search', ['type' => 'players']).'" class="text-accent hover:underline">'.e(__('football.compare.hint_link')).'</a>']) !!}</p>

        @if($player1 && $player2)
            @php
                $bio = function ($d) {
                    $p = $d['player'] ?? [];
                    $age = !empty($p['date_of_birth']) ? date_diff(date_create($p['date_of_birth']), date_create('today'))->y : null;
                    return [
                        'name' => $p['display_name'] ?? $p['name'] ?? __('football.compare.player'),
                        'img' => $p['image_path'] ?? null,
                        'pos' => $d['position'] ?? null,
                        'nat' => ($d['nationality'] ?? $d['country'] ?? [])['name'] ?? null,
                        'flag' => ($d['nationality'] ?? $d['country'] ?? [])['image_path'] ?? null,
                        'age' => $age,
                        'height' => $p['height'] ?? null,
                    ];
                };
                $b1 = $bio($player1); $b2 = $bio($player2);
            @endphp

            {{-- Bio headers --}}
            <div class="grid grid-cols-2 gap-4">
                @foreach([[$b1, 'emerald'], [$b2, 'blue']] as [$b, $c])
                    <div class="rounded-xl border border-line bg-surface p-5 text-center">
                        @if($b['img'])<img src="{{ $b['img'] }}" alt="" class="mx-auto h-20 w-20 rounded-xl object-cover border-2 border-{{ $c }}-500/40">@else<div class="mx-auto flex h-20 w-20 items-center justify-center rounded-xl bg-surface text-3xl"><x-icon name="user" class="h-4 w-4" /></div>@endif
                        <h3 class="mt-3 text-sm font-bold text-white truncate">{{ $b['name'] }}</h3>
                        <div class="mt-1 flex flex-wrap items-center justify-center gap-1.5 text-xs text-body">
                            @if($b['pos'])<span class="rounded-lg bg-surface px-1.5 py-0.5 font-bold">{{ $b['pos'] }}</span>@endif
                            @if($b['flag'])<span class="flex items-center gap-1"><img src="{{ $b['flag'] }}" class="h-3 w-3 rounded-sm object-cover">{{ $b['nat'] }}</span>@elseif($b['nat'])<span>{{ $b['nat'] }}</span>@endif
                            @if($b['age'])<span>· {{ $b['age'] }} {{ __('football.compare.years_short') }}</span>@endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Metric comparison bars --}}
            <div class="rounded-xl border border-line bg-surface p-6 space-y-4">
                <h3 class="text-sm font-bold text-white">{{ __('football.compare.career_stats') }}</h3>
                @foreach($metrics as [$label, $key, $higherBetter])
                    @php
                        $v1 = $a1[$key] ?? 0; $v2 = $a2[$key] ?? 0;
                        $max = max((float) $v1, (float) $v2, 0.0001);
                        $p1w = round(($v1 / $max) * 100); $p2w = round(($v2 / $max) * 100);
                        $win1 = $higherBetter ? $v1 > $v2 : $v1 < $v2;
                        $win2 = $higherBetter ? $v2 > $v1 : $v2 < $v1;
                    @endphp
                    <div>
                        <div class="mb-1 flex items-center justify-between text-xs font-bold">
                            <span class="font-mono {{ $win1 ? 'text-accent' : 'text-body' }}">{{ $v1 ?? '-' }}</span>
                            <span class="text-xs uppercase tracking-wider text-muted">{{ $label }}</span>
                            <span class="font-mono {{ $win2 ? 'text-steel' : 'text-body' }}">{{ $v2 ?? '-' }}</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <div class="flex flex-1 justify-end"><div class="h-2 rounded-l-full {{ $win1 ? 'bg-primary' : 'bg-surface' }}" style="width: {{ $p1w }}%"></div></div>
                            <div class="flex flex-1 justify-start"><div class="h-2 rounded-r-full {{ $win2 ? 'bg-steel text-ink' : 'bg-surface' }} text-ink" style="width: {{ $p2w }}%"></div></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @elseif($p1id || $p2id)
            <p class="rounded-xl border border-dashed border-line bg-surface p-8 text-center text-sm text-body">{!! __('football.compare.need_two', ['strong' => '<strong class="text-white">'.e(__('football.compare.need_two_strong')).'</strong>']) !!}</p>
        @endif
    </div>
@endsection
