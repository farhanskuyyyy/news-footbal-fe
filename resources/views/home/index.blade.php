@extends('layouts.app')

@section('title', __('home.title'))

@section('content')
    <div class="space-y-10">

        {{-- HERO --}}
        <div class="relative overflow-hidden rounded-xl border border-line bg-surface p-8 sm:p-12">
            <div class="relative z-10 max-w-2xl">
                <span class="kicker mb-3 inline-flex items-center gap-2 text-xs font-bold uppercase text-primary">
                    <span class="relative flex h-2 w-2"><span class=" absolute inline-flex h-full w-full rounded-lg opacity-75"></span><span class="relative inline-flex h-2 w-2 rounded-full "></span></span>
                    {{ __('home.hero.kicker') }}
                </span>
                <h1 class="text-3xl sm:text-5xl font-bold tracking-tight text-white">{{ __('home.hero.heading') }} <span class="text-accent">{{ __('home.hero.heading_accent') }}</span></h1>
                <p class="mt-3 text-sm sm:text-base text-body">{{ __('home.hero.subheading') }}</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('football.live') }}" class="inline-flex items-center gap-2 rounded-xl bg-primary hover:bg-accent px-4 py-2.5 text-sm font-bold text-white transition-all">{{ __('home.hero.cta_live') }}</a>
                    <a href="{{ route('football.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-line bg-surface px-4 py-2.5 text-sm font-bold text-white hover:border-line transition-colors">{{ __('home.hero.cta_portal') }}</a>
                </div>
            </div>
        </div>

        {{-- LIVE STRIP --}}
        @if(!empty($live))
            <section class="space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="flex items-center gap-2 text-lg font-bold text-white">
                        <span class="relative flex h-2.5 w-2.5"><span class=" absolute inline-flex h-full w-full rounded-lg bg-accent opacity-75"></span><span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-accent"></span></span>
                        {{ __('home.live.heading') }}
                    </h2>
                    <a href="{{ route('football.live') }}" class="text-xs font-bold text-accent hover:underline">{{ __('home.live.all') }}</a>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    @foreach($live as $f)
                        @include('football.partials.live-card', ['f' => $f])
                    @endforeach
                </div>
            </section>
        @endif

        {{-- MAIN GRID: today + featured --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- Today's matches --}}
            <section class="lg:col-span-2 space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-white">{{ __('home.today.heading') }}</h2>
                    <a href="{{ route('football.matchday') }}" class="text-xs font-bold text-accent hover:underline">{{ __('home.today.calendar') }}</a>
                </div>
                @if(!empty($today))
                    @php $byLeague = collect($today)->groupBy(fn ($f) => $f['league']['name'] ?? 'Lainnya'); @endphp
                    <div class="space-y-5">
                        @foreach($byLeague->take(4) as $leagueName => $rows)
                            <div class="space-y-2.5">
                                <h3 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-muted">
                                    @if(!empty($rows[0]['league']['image_path']))<img src="{{ $rows[0]['league']['image_path'] }}" alt="" class="h-4 w-4 object-contain">@endif
                                    {{ $leagueName }}
                                </h3>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    @foreach($rows->take(4) as $f)
                                        @include('football.partials.live-card', ['f' => $f])
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-line bg-surface p-8 text-center text-sm text-muted">{!! __('home.today.empty', ['link' => '<a href="'.route('football.matchday').'" class="text-accent hover:underline">'.e(__('home.today.empty_link')).'</a>']) !!}</div>
                @endif
            </section>

            {{-- Enabled leagues (status = true): standings + goal topscorers --}}
            <aside class="space-y-6" @if(count($featuredLeagues) > 0) x-data="{ lg: 0 }" @endif>
                @if(count($featuredLeagues) > 0)
                    {{-- League picker (only CMS-enabled leagues) --}}
                    @if(count($featuredLeagues) > 1)
                        <div class="relative">
                            <label for="home-league" class="sr-only">{{ __('home.featured.select_league') }}</label>
                            <select id="home-league" x-model.number="lg"
                                    class="w-full appearance-none rounded-xl border border-line bg-surface px-3.5 py-2.5 pr-9 text-sm font-bold text-white focus:border-line focus:outline-none focus:ring-2 focus:ring-line">
                                @foreach($featuredLeagues as $i => $fl)
                                    <option value="{{ $i }}">{{ $fl['league']['name'] }}</option>
                                @endforeach
                            </select>
                            <svg viewBox="0 0 24 24" fill="none" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted">
                                <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                    @endif

                    @foreach($featuredLeagues as $i => $fl)
                        <div @if(count($featuredLeagues) > 1) x-show="lg === {{ $i }}" x-cloak @endif class="space-y-6">
                            {{-- Mini standings --}}
                            <div class="rounded-xl border border-line bg-surface p-5">
                                <div class="mb-4 flex items-center gap-2">
                                    @if(!empty($fl['league']['image_path']))<img src="{{ $fl['league']['image_path'] }}" alt="" class="h-6 w-6 object-contain">@endif
                                    <div class="min-w-0">
                                        <span class="kicker block text-xs font-bold uppercase text-primary">{{ __('home.featured.standings') }}</span>
                                        <h3 class="truncate text-sm font-bold text-white">{{ $fl['league']['name'] }}</h3>
                                    </div>
                                </div>
                                @if(!empty($fl['standings']))
                                    <div class="space-y-1">
                                        @foreach($fl['standings'] as $st)
                                            <a href="{{ route('football.index', ['league_id' => $fl['league']['id'], 'season_id' => $fl['season']['id']]) }}"
                                               class="flex items-center gap-3 rounded-xl px-2.5 py-2 hover:bg-surface transition-colors">
                                                <span class="w-5 text-center font-mono text-xs font-bold text-muted">{{ $st['position'] ?? $loop->iteration }}</span>
                                                @if(!empty($st['team']['image_path']))<img src="{{ $st['team']['image_path'] }}" alt="" class="h-5 w-5 object-contain">@else<span class="h-5 w-5"></span>@endif
                                                <span class="flex-1 truncate text-xs font-bold text-white">{{ $st['team']['name'] ?? '-' }}</span>
                                                <span class="font-mono text-xs text-muted">{{ $st['played'] ?? 0 }}</span>
                                                <span class="w-6 text-right font-mono text-xs font-bold text-accent">{{ $st['points'] ?? 0 }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="px-2 py-3 text-xs text-muted">{{ __('home.featured.standings_empty') }}</p>
                                @endif
                                <a href="{{ route('football.index', ['league_id' => $fl['league']['id'], 'season_id' => $fl['season']['id']]) }}" class="mt-3 block text-center text-xs font-bold text-accent hover:underline">{{ __('home.featured.standings_full') }}</a>
                            </div>

                            {{-- Goal topscorers --}}
                            <div class="rounded-xl border border-line bg-surface p-5">
                                <div class="mb-3 flex items-center justify-between gap-2">
                                    <span class="kicker block text-xs font-bold uppercase text-primary">{{ __('home.featured.topscorers') }}</span>
                                    <span class="truncate text-xs font-bold text-muted">{{ $fl['league']['name'] }}</span>
                                </div>
                                @if(!empty($fl['topscorers']))
                                    <div class="space-y-2">
                                        @foreach($fl['topscorers'] as $ts)
                                            <a href="{{ route('football.player', $ts['player']['id'] ?? ($ts['player_id'] ?? 0)) }}" class="flex items-center gap-3 rounded-xl px-2 py-1.5 hover:bg-surface transition-colors">
                                                <span class="w-4 text-center font-mono text-xs font-bold text-muted">{{ $loop->iteration }}</span>
                                                @if(!empty($ts['player']['image_path']))<img src="{{ $ts['player']['image_path'] }}" alt="" class="h-7 w-7 rounded-lg object-cover border border-line">@else<div class="flex h-7 w-7 items-center justify-center rounded-lg bg-surface text-xs"><x-icon name="user" class="h-4 w-4" /></div>@endif
                                                <span class="flex-1 truncate text-xs font-bold text-white">{{ $ts['player']['display_name'] ?? $ts['player']['name'] ?? 'Pemain' }}</span>
                                                <span class="rounded-lg px-2 py-0.5 font-mono text-xs font-bold text-accent border border-line">{{ $ts['total'] ?? 0 }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                    <a href="{{ route('football.index', ['league_id' => $fl['league']['id'], 'season_id' => $fl['season']['id'], 'tab' => 'topscorers']) }}" class="mt-3 block text-center text-xs font-bold text-gold hover:underline">{{ __('home.featured.topscorers_full') }}</a>
                                @else
                                    <p class="px-2 py-3 text-xs text-muted">{{ __('home.featured.topscorers_empty') }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="rounded-xl border border-dashed border-line bg-surface p-8 text-center text-sm text-muted">
                        {!! __('home.featured.no_league', ['link' => '<span class="font-bold text-white">'.e(__('home.featured.no_league_link')).'</span>']) !!}
                    </div>
                @endif
            </aside>
        </div>

        {{-- LATEST NEWS --}}
        @if(!empty($news))
            <section class="space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-white">{{ __('home.news.heading') }}</h2>
                    <a href="{{ route('news.index') }}" class="text-xs font-bold text-accent hover:underline">{{ __('home.news.all') }}</a>
                </div>
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($news as $item)
                        <article class="group flex flex-col overflow-hidden rounded-xl border border-line bg-surface transition-all hover:border-line">
                            @if(!empty($item['url_to_image']))
                                <a href="{{ route('news.show', $item['id']) }}" class="block aspect-[16/9] overflow-hidden bg-ink">
                                    <img src="{{ $item['url_to_image'] }}" alt="" class="h-full w-full object-cover duration-500 group-" loading="lazy" onerror="this.closest('a').style.display='none'">
                                </a>
                            @endif
                            <div class="flex flex-1 flex-col p-4">
                                <span class="mb-2 inline-flex w-fit items-center rounded-lg px-2 py-0.5 text-xs font-bold uppercase tracking-wider text-accent border border-line">{{ $item['source'] ?? '—' }}</span>
                                <h3 class="mb-2 font-semibold leading-snug text-white">
                                    <a href="{{ route('news.show', $item['id']) }}" class="transition-colors group-hover:text-accent">{{ \Illuminate\Support\Str::limit($item['title'] ?? __('news.untitled'), 90) }}</a>
                                </h3>
                                <time class="mt-auto font-mono text-xs text-muted">{{ isset($item['published_at']) ? \Illuminate\Support\Carbon::parse($item['published_at'])->setTimezone('Asia/Jakarta')->locale(app()->getLocale())->translatedFormat('d M Y • H:i') . ' WIB' : '' }}</time>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection
