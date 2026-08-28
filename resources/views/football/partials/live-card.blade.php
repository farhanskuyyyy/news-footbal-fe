{{-- Renders one raw Sportmonks fixture (from livescores/fixtures-by-date proxy).
     Expects $f = a Sportmonks fixture array with participants, scores, state, league. --}}
@php
    $participants = $f['participants'] ?? [];
    $home = collect($participants)->first(fn ($p) => ($p['meta']['location'] ?? '') === 'home') ?? ($participants[0] ?? null);
    $away = collect($participants)->first(fn ($p) => ($p['meta']['location'] ?? '') === 'away') ?? ($participants[1] ?? null);

    // Current score by participant location
    $scores = $f['scores'] ?? [];
    $curHome = collect($scores)->first(fn ($s) => ($s['description'] ?? '') === 'CURRENT' && ($s['score']['participant'] ?? '') === 'home');
    $curAway = collect($scores)->first(fn ($s) => ($s['description'] ?? '') === 'CURRENT' && ($s['score']['participant'] ?? '') === 'away');
    $homeGoals = $curHome['score']['goals'] ?? null;
    $awayGoals = $curAway['score']['goals'] ?? null;
    $hasScore = ($homeGoals !== null && $awayGoals !== null);

    $stateCode = $f['state']['short_name'] ?? $f['state']['state'] ?? '';
    $isLive = in_array($stateCode, ['LIVE', 'INPLAY', '1H', '2H', 'HT', 'ET', 'PEN_LIVE']);
    $isFinished = in_array($stateCode, ['FT', 'AET', 'FT_PEN']);
    $leagueName = $f['league']['name'] ?? null;
    $leagueLogo = $f['league']['image_path'] ?? null;

    // Sportmonks starting_at is UTC — convert to WIB (Asia/Jakarta).
    $kick = !empty($f['starting_at'])
        ? \Illuminate\Support\Carbon::parse($f['starting_at'], 'UTC')->setTimezone('Asia/Jakarta')
        : null;
@endphp

<a href="{{ route('football.fixture', $f['id']) }}"
   class="group block rounded-2xl border border-slate-800 bg-slate-900/80 p-4 shadow-lg transition-all hover:border-slate-700 hover:bg-slate-900 hover:-translate-y-0.5">
    {{-- Meta row --}}
    <div class="mb-3 flex items-center justify-between border-b border-slate-800/70 pb-2.5 text-xs">
        <div class="flex items-center gap-2 min-w-0">
            @if($leagueLogo)
                <img src="{{ $leagueLogo }}" alt="" class="h-4 w-4 object-contain">
            @endif
            <span class="truncate text-slate-400 font-medium">{{ $leagueName ?? 'Pertandingan' }}</span>
        </div>
        <span class="font-mono font-black text-[10px] uppercase tracking-wider px-2 py-0.5 rounded-md
            {{ $isLive ? 'bg-red-600 text-white animate-pulse' : ($isFinished ? 'bg-slate-800 text-slate-300 border border-slate-700' : 'bg-blue-950 text-blue-300 border border-blue-800') }}">
            {{ $stateCode ?: 'NS' }}
        </span>
    </div>

    {{-- Teams + score --}}
    <div class="flex items-center justify-between gap-3">
        <div class="flex flex-1 items-center gap-2.5 min-w-0">
            @if(!empty($home['image_path']))
                <img src="{{ $home['image_path'] }}" alt="{{ $home['name'] ?? '' }}" class="h-8 w-8 object-contain">
            @else
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-800 text-xs">🛡️</div>
            @endif
            <span class="truncate text-sm font-bold text-slate-100 group-hover:text-emerald-400 transition-colors">{{ $home['name'] ?? 'Home' }}</span>
        </div>

        <div class="flex-shrink-0 rounded-xl border border-slate-800 bg-slate-950 px-3 py-1.5 font-mono min-w-[64px] text-center">
            @if($hasScore)
                <span class="text-lg font-black {{ $isLive ? 'text-emerald-400' : 'text-white' }}">{{ $homeGoals }} - {{ $awayGoals }}</span>
            @else
                <span class="text-xs font-bold text-slate-400">{{ $kick ? $kick->format('H:i') : 'VS' }}</span>
            @endif
        </div>

        <div class="flex flex-1 items-center justify-end gap-2.5 min-w-0 text-right">
            <span class="truncate text-sm font-bold text-slate-100 group-hover:text-emerald-400 transition-colors">{{ $away['name'] ?? 'Away' }}</span>
            @if(!empty($away['image_path']))
                <img src="{{ $away['image_path'] }}" alt="{{ $away['name'] ?? '' }}" class="h-8 w-8 object-contain">
            @else
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-800 text-xs">🛡️</div>
            @endif
        </div>
    </div>

    {{-- Kickoff date/time in WIB (only when not finished) --}}
    @if($kick && !$isFinished)
        <div class="mt-2.5 flex items-center justify-center gap-1.5 border-t border-slate-800/70 pt-2 text-[11px] text-slate-500">
            <svg viewBox="0 0 24 24" fill="none" class="h-3.5 w-3.5"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.6"/><path d="M12 7v5l3 2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <span class="font-mono">{{ $kick->locale('id')->translatedFormat('D, d M Y • H:i') }} WIB</span>
        </div>
    @endif
</a>
