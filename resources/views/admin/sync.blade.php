@extends('layouts.admin')
@section('title', __('cms.sync'))
@section('heading', __('cms.sync'))
@section('description', __('cms.sync_help'))
@section('content')
<div x-data="cmsMonitor(@js($running), @js($sync), @js($adminUnavailable))">
    <p x-show="unavailable" x-cloak role="alert" class="mb-4 text-sm text-danger">{{ __('common.empty.admin_unavailable') }}</p>
    @include('admin.partials.sync-table')
</div>
@endsection
