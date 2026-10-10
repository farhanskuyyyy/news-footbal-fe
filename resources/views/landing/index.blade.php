@extends('layouts.landing')
@section('content')
<section class="landing-container landing-hero">
    <div class="landing-hero-copy">
        <p class="landing-eyebrow">{{ __('landing.eyebrow') }}</p>
        <h1>{{ __('landing.heading_first') }} <br><span>{{ __('landing.heading_second') }}</span></h1>
        <p class="landing-lead">{{ __('landing.intro') }}</p>
        <div class="mt-8 flex flex-wrap items-center gap-4">
            <a href="{{ route('home') }}" class="landing-button">{{ __('landing.cta') }} <x-icon name="arrow-right" class="h-5 w-5" /></a>
            <a href="{{ route('football.live') }}" class="landing-text-link">{{ __('landing.live') }}</a>
        </div>
        <p class="mt-5 text-xs text-muted">{{ __('landing.access') }}</p>
    </div>
    <figure class="landing-tactics" x-data="{ paused: false }" :class="{ 'is-paused': paused }">
        <div class="landing-tactics-heading"><span>{{ __('landing.tactics_title') }}</span><span class="shrink-0 text-xs">{{ __('landing.pattern') }}</span></div>
        <div class="landing-pitch">
            <svg viewBox="0 0 600 430" role="img" aria-labelledby="pitch-title pitch-desc">
                <title id="pitch-title">{{ __('landing.pitch_title') }}</title>
                <desc id="pitch-desc">{{ __('landing.pitch_description') }}</desc>
                <g class="landing-pitch-lines" fill="none" stroke-width="1.5"><rect x="30" y="25" width="540" height="380" rx="2"/><path d="M300 25v380M30 125h85v180H30M570 125h-85v180h85M30 170h35v90H30M570 170h-35v90h35"/><circle cx="300" cy="215" r="52"/><circle cx="300" cy="215" r="2"/></g>
                <path class="landing-pass-path" d="M105 215 205 325 295 215 395 105 490 215 395 325 295 215 205 105 105 215" fill="none" stroke-width="2" stroke-dasharray="5 8"/>
                <g class="landing-opponents"><circle cx="280" cy="90" r="7"/><circle cx="335" cy="145" r="7"/><circle cx="375" cy="215" r="7"/><circle cx="335" cy="285" r="7"/><circle cx="280" cy="340" r="7"/></g>
                <g class="landing-players" stroke-width="2"><circle cx="105" cy="215" r="13"/><circle cx="205" cy="105" r="13"/><circle cx="205" cy="325" r="13"/><circle cx="295" cy="215" r="13"/><circle cx="395" cy="105" r="13"/><circle cx="395" cy="325" r="13"/><circle cx="490" cy="215" r="13"/></g>
                <circle class="landing-ball" r="7" fill="#E6B94A" stroke="#101714" stroke-width="2"/>
            </svg>
            <div class="landing-pitch-caption"><span>{{ __('landing.pitch_caption') }}</span><span class="landing-pitch-key"><span></span>{{ __('landing.ball') }}</span></div>
        </div>
        <figcaption class="landing-tactics-footer">
            <span>{{ __('landing.illustration') }}</span>
            <button type="button" x-cloak class="landing-motion-toggle" @click="paused = !paused" :aria-pressed="paused" x-text="paused ? @js(__('landing.play')) : @js(__('landing.pause'))">{{ __('landing.pause') }}</button>
        </figcaption>
    </figure>
</section>
<section id="the-game" class="landing-product">
    <div class="landing-container landing-product-layout">
        <div class="landing-product-intro">
            <p class="landing-eyebrow">{{ __('landing.product_eyebrow') }}</p>
            <h2>{{ __('landing.product_heading') }}</h2>
            <p class="mt-5 max-w-sm text-base leading-relaxed text-muted">{{ __('landing.product_intro') }}</p>
        </div>
        <div>
            @foreach(['matches' => ['football.matchday', 'calendar'], 'leagues' => ['football.index', 'chart'], 'players' => ['football.search', 'user'], 'news' => ['news.index', 'ball']] as $key => [$route, $icon])
                <a href="{{ route($route, $key === 'players' ? ['type' => 'players'] : []) }}" class="landing-feature-row">
                    <span class="landing-feature-number">0{{ $loop->iteration }}</span>
                    <div><h3>{{ __('landing.'.$key.'_title') }}</h3><p>{{ __('landing.'.$key.'_description') }}</p></div>
                    <x-icon :name="$icon" class="h-6 w-6 text-link" />
                </a>
            @endforeach
        </div>
    </div>
</section>
<section id="competitions" class="landing-container landing-coverage">
    <div class="landing-coverage-heading"><div><p class="landing-eyebrow">{{ __('landing.coverage_eyebrow') }}</p><h2>{{ __('landing.coverage_heading') }}</h2></div><p>{{ __('landing.coverage_intro') }}</p></div>
    <div class="landing-competition-list">
        @forelse($leagues as $league)
            <a href="{{ route('football.index', ['league_id' => $league['id']]) }}" class="landing-competition">
                <span class="entity-mark" aria-hidden="true">@if(!empty($league['image_path']))<img src="{{ $league['image_path'] }}" alt="" loading="lazy">@endif<x-icon name="shield" class="h-5 w-5 text-muted" /></span>
                <span>{{ $league['name'] }}</span>
                <x-icon name="arrow-right" class="ml-auto h-4 w-4 text-muted" />
            </a>
        @empty <p class="p-5 text-sm text-muted">{{ __('landing.coverage_empty') }}</p> @endforelse
    </div>
</section>
<section class="landing-container landing-outro">
    <div><p class="landing-eyebrow">{{ __('landing.outro_eyebrow') }}</p><h2>{{ __('landing.outro_heading') }}</h2><p>{{ __('landing.outro_text') }}</p></div>
    <a href="{{ route('home') }}" class="landing-button">{{ __('landing.cta') }} <x-icon name="arrow-right" class="h-5 w-5" /></a>
</section>
@endsection
