{{--
    One confirmed transfer row.

    $tr  raw Sportmonks transfer item
--}}
@php
    $pl = $tr['player'] ?? [];
    $from = $tr['fromteam'] ?? $tr['from_team'] ?? [];
    $to = $tr['toteam'] ?? $tr['to_team'] ?? [];
    $typeName = $tr['type']['name'] ?? null;
    $amount = $tr['amount'] ?? null;
    $fee = null;
    if (! empty($amount) && $amount > 0) {
        if ($amount >= 1000000) {
            $fee = '€'.rtrim(rtrim(number_format($amount / 1000000, 1), '0'), '.').'M';
        } elseif ($amount >= 1000) {
            $fee = '€'.round($amount / 1000).'K';
        } else {
            $fee = '€'.number_format($amount);
        }
    }
    $tLower = strtolower($typeName ?? '');
    $isLoan = str_contains($tLower, 'loan') || str_contains($tLower, 'pinjam');
@endphp
<div class="flex flex-col gap-4 rounded-xl border border-line bg-surface p-4 transition-all hover:border-line sm:flex-row sm:items-center sm:justify-between">
    {{-- Player --}}
    <div class="flex items-center gap-3 min-w-0 sm:w-1/3">
        <a href="{{ route('football.player', $pl['id'] ?? 0) }}" class="shrink-0">
            @if(!empty($pl['image_path']))
                <img src="{{ $pl['image_path'] }}" alt="" class="h-11 w-11 rounded-lg object-cover border border-line">
            @else
                <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-surface text-lg"><x-icon name="user" class="h-4 w-4" /></div>
            @endif
        </a>
        <div class="min-w-0">
            <a href="{{ route('football.player', $pl['id'] ?? 0) }}" class="block truncate text-sm font-bold text-white hover:text-accent transition-colors">{{ $pl['display_name'] ?? $pl['name'] ?? __('football.transfers.player') }}</a>
            <span class="font-mono text-xs text-white0">
                {{ !empty($tr['date']) ? \Illuminate\Support\Carbon::parse($tr['date'])->locale(app()->getLocale())->translatedFormat('d M Y') : __('football.transfers.official') }}
            </span>
        </div>
    </div>

    {{-- From <x-icon name="arrow-right" class="h-4 w-4" /> To --}}
    <div class="flex flex-1 items-center justify-center gap-2.5 text-xs font-bold">
        <a href="{{ !empty($from['id']) ? route('football.team', $from['id']) : '#' }}" class="flex items-center gap-1.5 rounded-lg bg-ink px-2.5 py-1.5 text-white border border-line hover:border-line transition-colors max-w-[42%] min-w-0">
            @if(!empty($from['image_path']))<img src="{{ $from['image_path'] }}" alt="" class="h-4 w-4 object-contain shrink-0">@endif
            <span class="truncate">{{ $from['name'] ?? __('football.transfers.from_club') }}</span>
        </a>
        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 shrink-0 text-accent"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        <a href="{{ !empty($to['id']) ? route('football.team', $to['id']) : '#' }}" class="flex items-center gap-1.5 rounded-lg border border-line px-2.5 py-1.5 text-accent transition-colors hover:border-line max-w-[42%] min-w-0">
            @if(!empty($to['image_path']))<img src="{{ $to['image_path'] }}" alt="" class="h-4 w-4 object-contain shrink-0">@endif
            <span class="truncate">{{ $to['name'] ?? __('football.transfers.to_club') }}</span>
        </a>
    </div>

    {{-- Type + fee --}}
    <div class="flex items-center justify-end gap-2 sm:w-1/5">
        @if($typeName)
            <span class="rounded-lg px-2 py-0.5 text-xs font-bold uppercase tracking-wider {{ $isLoan ? 'text-steel border border-line' : 'bg-surface text-white border border-line' }}">{{ $typeName }}</span>
        @endif
        @if($fee)
            <span class="rounded-lg px-2 py-0.5 font-mono text-xs font-bold text-gold border border-line">{{ $fee }}</span>
        @endif
    </div>
</div>
