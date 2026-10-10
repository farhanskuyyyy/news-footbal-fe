@extends('layouts.admin')
@section('title', __('cms.scrapers'))
@section('heading', __('cms.scrapers'))
@section('description', __('cms.scrapers_help'))
@section('content')
<div class="space-y-8" x-data="cmsMonitor(@js($running), @js($sync), @js($adminUnavailable))">
    @include('admin.partials.running')
    <section class="cms-section">
        <h2 class="cms-panel-header text-lg font-semibold">{{ __('admin.scraper.heading') }}</h2>
        <div class="divide-y divide-line">
            @foreach($jobs as $job)
                <form method="POST" action="{{ route('admin.scrape') }}" class="cms-job-row">
                    @csrf<input type="hidden" name="job" value="{{ $job }}">
                    <div class="min-w-0"><h3 class="break-words text-sm font-semibold">{{ $job }}</h3><p class="mt-1 text-xs leading-relaxed text-muted">{{ __('cms.job_help.'.$job) }}</p></div>
                    <div class="flex flex-wrap items-center gap-4">
                        @if(in_array($job, ['football', 'fixture-details']))<label class="flex min-h-11 items-center gap-2 text-xs text-muted"><input type="checkbox" name="force" value="1" class="h-4 w-4 accent-primary">{{ __('admin.scraper.force_ttl') }}</label>@endif
                        <button class="cms-button" aria-label="{{ __('admin.scraper.run').': '.$job }}">{{ __('admin.scraper.run') }}</button>
                    </div>
                </form>
            @endforeach
        </div>
    </section>
    <section class="cms-section" x-data="cmsFootballSeasons(@js(url('/admin/leagues')))">
        <div class="cms-panel-header">
        <h2 class="text-lg font-semibold">{{ __('admin.football.heading') }}</h2>
        <p class="mt-2 text-sm text-muted">{{ __('admin.football.description') }}</p>
        </div>
        <form method="POST" action="{{ route('admin.scrape.football') }}" class="mt-6 grid grid-cols-1 items-end gap-4 sm:grid-cols-2 xl:grid-cols-[1fr_1fr_auto]">
            @csrf
            <div><label for="admin-league" class="mb-2 block text-sm font-medium">{{ __('admin.football.league') }}</label>
                <select id="admin-league" name="league_id" x-model="leagueId" @change="loadSeasons()" class="cms-input">
                    <option value="">{{ __('admin.football.all_leagues') }}</option>
                    @foreach($leagues as $league) @if(!empty($league['status']))<option value="{{ $league['id'] }}">{{ $league['name'] }}</option>@endif @endforeach
                </select>
            </div>
            <div :aria-busy="loading"><label for="admin-season" class="mb-2 block text-sm font-medium">{{ __('admin.football.season') }}</label>
                <div x-show="loading" x-cloak class="flex h-11 items-center border border-line bg-surface px-3" aria-hidden="true"><span class="skeleton-block h-4 w-2/3"></span></div>
                <p x-show="loading" x-cloak role="status" class="sr-only">{{ __('common.loading_seasons') }}</p>
                <select id="admin-season" name="season_id" x-show="!loading" x-model="seasonId" :disabled="!leagueId || loading || failed" class="cms-input disabled:opacity-50">
                    <option value="">{{ __('admin.football.current_season') }}</option>
                    <template x-for="season in seasons" :key="season.id"><option :value="season.id" x-text="season.name + (season.is_current ? @js(__('admin.football.season_current_suffix')) : '')"></option></template>
                </select>
                <p x-show="failed" x-cloak role="alert" class="mt-2 text-xs text-danger">{{ __('common.seasons_unavailable') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-4"><label class="flex min-h-11 items-center gap-2 text-xs text-muted"><input type="checkbox" name="force" value="1" class="h-4 w-4 accent-primary">{{ __('admin.scraper.force') }}</label><button :disabled="loading || failed" class="cms-button cms-button-primary disabled:opacity-50">{{ __('admin.scraper.run') }}</button></div>
        </form>
    </section>
    <section class="cms-section">
        <h2 class="cms-panel-header text-lg font-semibold">{{ __('admin.scraper.scrape_fixture') }}</h2>
        <form method="POST" action="{{ route('admin.scrape.fixture') }}" class="mt-5 flex flex-wrap items-end gap-3">
            @csrf
            <div class="w-full max-w-sm"><label for="fixture-id" class="mb-2 block text-sm font-medium">{{ __('admin.scraper.fixture_id') }}</label><input id="fixture-id" type="number" min="1" name="fixture_id" required class="cms-input"></div>
            <button class="cms-button">{{ __('admin.scraper.scrape_fixture') }}</button>
        </form>
    </section>
</div>
@endsection
