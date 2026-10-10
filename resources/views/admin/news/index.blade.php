@extends('layouts.admin')
@section('title', __('cms.news'))
@section('heading', __('cms.news'))
@section('description', __('cms.news_help'))
@section('actions')<form method="POST" action="{{ route('admin.news.refresh') }}">@csrf<button class="cms-button cms-button-primary">{{ __('admin.news.refresh') }}</button></form>@endsection
@section('content')
<x-cms-search :label="__('cms.search_news')" :query="$query" />
@if($unavailable)
    <div role="alert" class="cms-card"><p class="text-sm text-danger">{{ __('cms.news_unavailable') }}</p><a href="{{ url()->full() }}" class="cms-button mt-4">{{ __('cms.reload') }}</a></div>
@else
    <div class="cms-table-wrap" role="region" aria-label="{{ __('cms.news') }}" tabindex="0">
        <table class="cms-table cms-news-table">
            <thead><tr><th>{{ __('cms.title') }}</th><th>{{ __('cms.source') }}</th><th>{{ __('cms.published') }}</th><th>{{ __('cms.actions') }}</th></tr></thead>
            <tbody>
                @forelse($news as $item)
                    <tr><th scope="row" class="cms-headline"><a href="{{ route('admin.news.show', $item['id']) }}" class="hover:text-link">{{ $item['title'] ?? __('news.untitled') }}</a></th><td data-label="{{ __('cms.source') }}">{{ $item['source'] ?? __('cms.unknown') }}</td><td data-label="{{ __('cms.published') }}" class="whitespace-nowrap text-muted">{{ !empty($item['published_at']) ? \Illuminate\Support\Carbon::parse($item['published_at'])->timezone('Asia/Jakarta')->format('d M Y H:i').' WIB' : __('cms.unknown') }}</td><td data-label="{{ __('cms.actions') }}"><a href="{{ route('admin.news.show', $item['id']) }}" class="cms-button">{{ __('cms.read') }}</a></td></tr>
                @empty<tr><td colspan="4" class="text-muted">{{ __('cms.news_empty') }}</td></tr>@endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $news->links() }}</div>
@endif
@endsection
