@extends('layouts.admin')
@section('title', __('cms.overview'))
@section('heading', __('cms.overview'))
@section('description', __('cms.overview_help'))
@section('actions')<a href="{{ route('admin.scrapers') }}" class="cms-button cms-button-primary">{{ __('cms.scrapers') }}</a>@endsection
@section('content')
<div class="space-y-8" x-data="cmsMonitor(@js($running), @js($sync), @js($adminUnavailable))">
    @include('admin.partials.running')
    @include('admin.partials.sync-table')
</div>
@endsection
