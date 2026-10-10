@extends('layouts.app')
@section('title', __('common.nav.home'))
@section('content')
<div class="home-layout">
    <aside class="home-leagues min-w-0">
        <form action="{{ route('football.index') }}" method="GET" class="portal-card home-league-picker">
            <label for="home-league" class="text-sm font-semibold">{{ __('common.nav.portal') }}</label>
            @if(count($leagues))
                <x-league-select id="home-league" name="league_id" :leagues="$leagues" />
                <button class="rounded-lg border border-control px-4 text-sm font-semibold text-link hover:bg-canvas">{{ __('home.featured.open_league') }}</button>
            @else <p class="text-sm text-muted">{{ __('common.empty.no_data') }}</p> @endif
        </form>
    </aside>
    <div class="home-matches min-w-0">
        <section>
            <div class="portal-card home-calendar mb-4">
            <div class="portal-card-heading flex flex-wrap items-center justify-between gap-2 px-4 py-3 sm:px-5">
                <h1 class="text-2xl font-bold tracking-tight">{{ __('home.today.heading') }}</h1>
                <a href="{{ route('football.matchday') }}" class="flex min-h-11 items-center text-sm font-semibold text-link">{{ __('home.today.calendar') }}</a>
            </div>
            <nav aria-label="{{ __('common.nav.schedule') }}" class="flex overflow-x-auto border-t border-divider">
                @foreach([-1, 0, 1] as $offset)
                    @php $day = now('Asia/Jakarta')->addDays($offset); @endphp
                    <a href="{{ route('football.matchday', ['date' => $day->format('Y-m-d')]) }}" class="flex min-h-11 flex-1 items-center justify-center whitespace-nowrap px-3 text-sm {{ $offset === 0 ? 'border-b-2 border-link font-semibold text-link' : 'text-muted' }}">{{ $day->locale(app()->getLocale())->translatedFormat('D, d M') }}</a>
                @endforeach
            </nav>
            </div>
            @include('football.partials.match-groups', ['fixtures' => $matches, 'showLiveFilter' => true, 'showLeagueLinks' => false])
        </section>
    </div>
    <aside class="min-w-0 space-y-6">
        @include('home.partials.standings')
        <section class="portal-card home-news">
            <div class="portal-card-heading flex items-center justify-between gap-2 px-4 py-2"><h2 class="text-base font-semibold">{{ __('home.news.heading') }}</h2><a href="{{ route('news.index') }}" class="flex min-h-11 items-center text-xs text-link">{{ __('common.actions.all') }}</a></div>
            <div class="border-t border-divider">
                @forelse($news as $item)
                    <article class="home-news-item border-b border-divider p-4 last:border-b-0">
                        <div class="min-w-0">
                            <p class="mb-2 text-xs text-muted">{{ $item['source'] ?? '—' }}</p>
                            <h3 class="text-sm font-semibold leading-snug"><a href="{{ route('news.show', $item['id']) }}" class="hover:text-link">{{ $item['title'] ?? __('news.untitled') }}</a></h3>
                        </div>
                        @if(!empty($item['url_to_image']))<img src="{{ $item['url_to_image'] }}" alt="" loading="lazy" class="h-20 w-20 rounded-lg object-cover">@endif
                    </article>
                @empty <p @if($newsUnavailable) role="alert" @endif class="p-4 text-sm {{ $newsUnavailable ? 'text-danger' : 'text-muted' }}">{{ $newsUnavailable ? __('news.unavailable_heading') : __('common.empty.no_data') }}</p> @endforelse
            </div>
        </section>
    </aside>
</div>
@endsection
