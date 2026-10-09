@extends('layouts.app')
@section('title', __('common.nav.home'))
@section('content')
<div class="home-layout">
    <aside class="home-leagues min-w-0">
        <h2 class="mb-3 text-sm font-semibold">{{ __('common.nav.portal') }}</h2>
        <nav class="rounded-xl border border-divider bg-surface" aria-label="{{ __('common.nav.portal') }}">
            @forelse($leagues as $league)
                <a href="{{ route('football.index', ['league_id' => $league['id']]) }}" class="flex min-h-11 items-center gap-2 border-b border-divider px-3 py-3 text-sm last:border-b-0 hover:bg-canvas">
                    @if(!empty($league['image_path']))<img src="{{ $league['image_path'] }}" alt="" class="h-5 w-5 object-contain">@endif
                    <span class="min-w-0 break-words">{{ $league['name'] }}</span>
                </a>
            @empty <p class="p-4 text-sm text-muted">{{ __('common.empty.no_data') }}</p> @endforelse
        </nav>
    </aside>
    <div class="min-w-0 space-y-6">
        <section>
            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                <h1 class="text-xl font-semibold">{{ __('home.today.heading') }}</h1>
                <a href="{{ route('football.matchday') }}" class="flex min-h-11 items-center text-sm font-semibold text-link">{{ __('home.today.calendar') }}</a>
            </div>
            <nav aria-label="{{ __('common.nav.schedule') }}" class="mb-4 flex overflow-x-auto rounded-lg border border-divider bg-surface">
                @foreach([-1, 0, 1] as $offset)
                    @php $day = now('Asia/Jakarta')->addDays($offset); @endphp
                    <a href="{{ route('football.matchday', ['date' => $day->format('Y-m-d')]) }}" class="flex min-h-11 flex-1 items-center justify-center whitespace-nowrap px-3 text-sm {{ $offset === 0 ? 'border-b-2 border-link font-semibold text-link' : 'text-muted' }}">{{ $day->locale(app()->getLocale())->translatedFormat('D, d M') }}</a>
                @endforeach
            </nav>
            @include('football.partials.match-groups', ['fixtures' => $today])
        </section>
        @if(count($live))
            <section>
                <div class="mb-3 flex items-center justify-between gap-3"><h2 class="text-lg font-semibold text-danger">{{ __('home.live.heading') }}</h2><a href="{{ route('football.live') }}" class="flex min-h-11 items-center text-sm text-link">{{ __('home.live.all') }}</a></div>
                @include('football.partials.match-groups', ['fixtures' => $live])
            </section>
        @endif
    </div>
    <aside class="min-w-0 space-y-6">
        <section>
            <div class="mb-3 flex items-center justify-between gap-2"><h2 class="text-sm font-semibold">{{ __('home.news.heading') }}</h2><a href="{{ route('news.index') }}" class="flex min-h-11 items-center text-xs text-link">{{ __('common.actions.all') }}</a></div>
            <div class="overflow-hidden rounded-xl border border-divider bg-surface">
                @forelse($news as $item)
                    <article class="border-b border-divider p-4 last:border-b-0">
                        @if(!empty($item['url_to_image']))<img src="{{ $item['url_to_image'] }}" alt="" loading="lazy" class="mb-3 aspect-video w-full rounded-lg object-cover">@endif
                        <p class="mb-2 text-xs text-muted">{{ $item['source'] ?? '—' }}</p>
                        <h3 class="text-sm font-semibold leading-snug"><a href="{{ route('news.show', $item['id']) }}" class="hover:text-link">{{ $item['title'] ?? __('news.untitled') }}</a></h3>
                    </article>
                @empty <p @if($newsUnavailable) role="alert" @endif class="p-4 text-sm {{ $newsUnavailable ? 'text-danger' : 'text-muted' }}">{{ $newsUnavailable ? __('news.unavailable_heading') : __('common.empty.no_data') }}</p> @endforelse
            </div>
        </section>
        @foreach($featuredLeagues as $fl)
            <section class="rounded-xl border border-divider bg-surface p-4">
                <h2 class="mb-3 text-sm font-semibold">{{ $fl['league']['name'] }} · {{ __('home.featured.standings') }}</h2>
                @forelse($fl['standings'] as $standing)
                    <div class="flex items-center gap-2 border-b border-divider py-3 text-xs last:border-b-0"><span class="w-4 tabular-nums text-muted">{{ $standing['position'] ?? $loop->iteration }}</span><span class="min-w-0 flex-1 truncate">{{ $standing['team']['name'] ?? '—' }}</span><span class="font-semibold tabular-nums">{{ $standing['points'] ?? '—' }}</span></div>
                @empty <p class="text-xs text-muted">{{ __('home.featured.standings_empty') }}</p> @endforelse
                <a href="{{ route('football.index', ['league_id' => $fl['league']['id'], 'season_id' => $fl['season']['id'], 'tab' => 'standings']) }}" class="mt-2 flex min-h-11 items-center text-xs font-semibold text-link">{{ __('home.featured.standings_full') }}</a>
                <h3 class="mt-4 border-t border-divider pt-4 text-sm font-semibold">{{ __('home.featured.topscorers') }}</h3>
                @forelse($fl['topscorers'] as $scorer)
                    @php $playerId = $scorer['player']['id'] ?? $scorer['player_id'] ?? null; @endphp
                    <div class="flex min-h-11 items-center justify-between gap-2 text-xs">
                        @if($playerId)<a href="{{ route('football.player', $playerId) }}" class="min-w-0 truncate hover:text-link">{{ $scorer['player']['display_name'] ?? $scorer['player']['name'] ?? '—' }}</a>@else<span>—</span>@endif
                        <span class="font-semibold tabular-nums">{{ $scorer['total'] ?? '—' }}</span>
                    </div>
                @empty <p class="mt-2 text-xs text-muted">{{ __('home.featured.topscorers_empty') }}</p> @endforelse
                <a href="{{ route('football.index', ['league_id' => $fl['league']['id'], 'season_id' => $fl['season']['id'], 'tab' => 'topscorers']) }}" class="flex min-h-11 items-center text-xs text-link">{{ __('home.featured.topscorers_full') }}</a>
            </section>
        @endforeach
    </aside>
</div>
@endsection
