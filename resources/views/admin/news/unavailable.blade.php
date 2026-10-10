@extends('layouts.admin')
@section('title', __('cms.news'))
@section('heading', __('cms.news'))
@section('content')
<div class="cms-card max-w-3xl">
<p role="alert" class="text-sm text-danger">{{ __('cms.news_unavailable') }}</p>
<div class="mt-6 flex flex-wrap gap-3"><a href="{{ url()->current() }}" class="cms-button">{{ __('cms.reload') }}</a><a href="{{ route('admin.news.index') }}" class="cms-button">{{ __('cms.back_news') }}</a></div>
</div>
@endsection
