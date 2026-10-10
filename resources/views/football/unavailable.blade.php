@extends('layouts.app')
@section('seo_view', 'football.unavailable')
@section('title', __('common.empty.unavailable'))
@section('content')
<section class="mx-auto max-w-xl rounded-xl border border-divider bg-surface p-6">
    <h1 class="text-xl font-semibold">{{ __('common.empty.unavailable') }}</h1>
    <a href="{{ request()->fullUrl() }}" class="mt-4 inline-flex min-h-11 items-center rounded-lg border border-control px-4 text-sm font-semibold text-link">{{ __('common.actions.refresh') }}</a>
</section>
@endsection
