@php
    $participants = $f['participants'] ?? [];
    $home = $f['home_team'] ?? collect($participants)->first(fn ($p) => ($p['meta']['location'] ?? '') === 'home') ?? ($participants[0] ?? []);
    $away = $f['away_team'] ?? collect($participants)->first(fn ($p) => ($p['meta']['location'] ?? '') === 'away') ?? ($participants[1] ?? []);
    $scores = collect($f['scores'] ?? []);
    $homeGoals = $f['current_home_score'] ?? $scores->first(fn ($s) => ($s['description'] ?? '') === 'CURRENT' && ($s['score']['participant'] ?? '') === 'home')['score']['goals'] ?? null;
    $awayGoals = $f['current_away_score'] ?? $scores->first(fn ($s) => ($s['description'] ?? '') === 'CURRENT' && ($s['score']['participant'] ?? '') === 'away')['score']['goals'] ?? null;
    $hasScore = $homeGoals !== null && $awayGoals !== null;
    $stateCode = $f['state']['short_name'] ?? $f['state']['state'] ?? '';
    $isLive = in_array($stateCode, ['1st', '2nd', 'HT', 'BRK', 'et', 'ETB', '2et', 'PEN', 'PENB']);
    $isFinished = in_array($stateCode, ['FT', 'AET', 'FTP']);
    $homeWon = $isFinished && $hasScore && $homeGoals > $awayGoals;
    $awayWon = $isFinished && $hasScore && $awayGoals > $homeGoals;
    $kick = !empty($f['starting_at']) ? \Illuminate\Support\Carbon::parse($f['starting_at'], 'UTC')->setTimezone('Asia/Jakarta') : null;
    $names = explode(' vs ', $f['name'] ?? '');
    $homeName = $home['name'] ?? ($names[0] ?: __('football.card.home'));
    $awayName = $away['name'] ?? ($names[1] ?? __('football.card.away'));
@endphp
<a href="{{ route('football.fixture', $f['id']) }}" data-match-row data-match-search="{{ $homeName }} {{ $awayName }} {{ $f['league']['name'] ?? '' }}" class="match-row border-b border-divider px-3 py-3 last:border-b-0 hover:bg-canvas">
    <div class="text-center text-xs tabular-nums {{ $isLive ? 'font-semibold text-danger' : 'text-muted' }}">
        <span class="block">{{ $stateCode ?: 'NS' }}</span>
        @if($kick)<time datetime="{{ $kick->toIso8601String() }}" title="{{ $kick->format('d M Y H:i') }} WIB" class="mt-1 block">{{ $kick->format('H:i') }} WIB</time>@endif
    </div>
    <div class="min-w-0 space-y-2 text-sm">
        @foreach([$home, $away] as $team)
            <div class="flex min-w-0 items-center gap-2">
                @if(!empty($team['image_path']))<img src="{{ $team['image_path'] }}" alt="" loading="lazy" class="h-5 w-5 shrink-0 object-contain">@else<x-icon name="shield" class="h-5 w-5 text-muted" />@endif
                <span class="truncate {{ ($loop->first && $homeWon) || (!$loop->first && $awayWon) ? 'font-semibold' : 'font-medium' }}">{{ $loop->first ? $homeName : $awayName }}</span>
            </div>
        @endforeach
    </div>
    <div class="space-y-2 text-right text-sm font-semibold tabular-nums {{ $isLive ? 'text-danger' : 'text-foreground' }}" aria-label="{{ $hasScore ? $homeGoals.' - '.$awayGoals : __('common.empty.no_data') }}">
        <span class="block">{{ $hasScore ? $homeGoals : '—' }}</span>
        <span class="block">{{ $hasScore ? $awayGoals : '—' }}</span>
    </div>
</a>
