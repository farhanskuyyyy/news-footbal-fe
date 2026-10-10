@extends('layouts.app')
@section('title', __('football.live.title'))
@section('content')
<div class="mx-auto max-w-3xl space-y-4">
    <div class="portal-card flex flex-wrap items-center justify-between gap-3 p-5">
        <div><h1 class="text-2xl font-semibold">{{ __('football.live.heading') }}</h1><p class="mt-1 text-sm text-muted">{{ __('football.live.subheading') }}</p></div>
        <a href="{{ route('football.live') }}" class="flex min-h-11 items-center rounded-lg border border-control bg-surface px-4 text-sm font-semibold text-link">{{ __('common.actions.refresh') }}</a>
    </div>
    @include('football.partials.match-groups', ['fixtures' => $matches, 'emptyMessage' => __('football.live.empty')])
</div>
@endsection
