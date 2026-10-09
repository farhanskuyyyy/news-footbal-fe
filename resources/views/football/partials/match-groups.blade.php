@php $groups = collect($fixtures)->groupBy(fn ($f) => $f['league']['id'] ?? $f['league_id'] ?? $f['league']['name'] ?? '—'); @endphp
<div data-match-board class="space-y-3">
    @if(count($fixtures))
        <label hidden data-match-filter-control class="block text-xs font-semibold text-muted">{{ __('common.nav.filter') }}<input type="search" data-match-filter class="mt-2 w-full rounded-lg border border-control bg-surface px-3 text-sm text-foreground"></label>
        @foreach($groups as $rows)
            <details open data-match-group class="overflow-hidden rounded-xl border border-divider bg-surface">
                <summary class="min-h-11 bg-canvas px-3 py-3 text-sm font-semibold">{{ $rows->first()['league']['name'] ?? '—' }} <span class="ml-2 text-xs font-normal text-muted">{{ count($rows) }}</span></summary>
                @foreach($rows->sortBy('starting_at') as $f) @include('football.partials.live-card', ['f' => $f]) @endforeach
                @php $leagueId = $rows->first()['league']['id'] ?? $rows->first()['league_id'] ?? null; @endphp
                @if($leagueId)<a href="{{ route('football.index', ['league_id' => $leagueId]) }}" class="flex min-h-11 items-center border-t border-divider px-3 text-xs font-semibold text-link">{{ __('common.nav.portal') }}: {{ $rows->first()['league']['name'] ?? '—' }}</a>@endif
            </details>
        @endforeach
        <p data-filter-empty hidden role="status" class="rounded-xl border border-divider bg-surface p-6 text-sm text-muted">{{ __('common.nav.filter_empty') }}</p>
    @elseif(!($footballUnavailable ?? false))
        <p class="rounded-xl border border-divider bg-surface p-6 text-sm text-muted">{{ $emptyMessage ?? __('football.matchday.empty') }}</p>
    @endif
</div>
