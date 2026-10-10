@extends('layouts.admin')
@section('title', __('cms.leagues'))
@section('heading', __('cms.leagues'))
@section('description', __('cms.leagues_help'))
@section('actions')<a href="{{ route('admin.scrapers') }}" class="cms-button">{{ __('cms.scrapers') }}</a>@endsection
@section('content')
<x-cms-search :label="__('cms.search_leagues')" :query="$query" />
@if($unavailable)
    <div class="cms-card">
    <p role="alert" class="mb-4 text-sm text-danger">{{ __('common.empty.unavailable') }}</p>
    <a href="{{ url()->full() }}" class="cms-button">{{ __('cms.reload') }}</a>
    </div>
@else
    <div class="cms-table-wrap" role="region" aria-label="{{ __('cms.leagues') }}" tabindex="0">
        <table class="cms-table">
            <thead><tr><th>{{ __('admin.leagues.th.league') }}</th><th>{{ __('admin.leagues.th.sportmonks_active') }}</th><th>{{ __('admin.leagues.th.seasons') }}</th><th>{{ __('admin.leagues.th.scrape_status') }}</th><th>{{ __('cms.actions') }}</th></tr></thead>
            <tbody>
                @forelse($leagues as $league)
                    <tr>
                        <th scope="row"><span class="flex items-center gap-2">@if(!empty($league['image_path']))<img src="{{ $league['image_path'] }}" alt="" class="h-5 w-5 object-contain">@endif{{ $league['name'] }}</span></th>
                        <td data-label="{{ __('admin.leagues.th.sportmonks_active') }}">{{ !empty($league['active']) ? __('admin.leagues.yes') : __('admin.leagues.no') }}</td>
                        <td data-label="{{ __('admin.leagues.th.seasons') }}" class="tabular-nums">{{ $league['seasons_count'] ?? __('cms.unknown') }}</td>
                        <td data-label="{{ __('admin.leagues.th.scrape_status') }}" class="{{ !empty($league['status']) ? 'text-link' : 'text-muted' }}">{{ !empty($league['status']) ? __('admin.leagues.enabled') : __('admin.leagues.disabled') }}</td>
                        <td data-label="{{ __('cms.actions') }}"><form method="POST" action="{{ route('admin.leagues.toggle') }}">@csrf<input type="hidden" name="league_id" value="{{ $league['id'] }}"><input type="hidden" name="status" value="{{ !empty($league['status']) ? '0' : '1' }}"><button class="cms-button" aria-label="{{ (!empty($league['status']) ? __('admin.leagues.disable') : __('admin.leagues.enable')).': '.$league['name'] }}">{{ !empty($league['status']) ? __('admin.leagues.disable') : __('admin.leagues.enable') }}</button></form></td>
                    </tr>
                @empty<tr><td colspan="5" class="text-muted">{{ __('cms.leagues_empty') }}</td></tr>@endforelse
            </tbody>
        </table>
    </div>
@endif
@endsection
