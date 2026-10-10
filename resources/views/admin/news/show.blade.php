@extends('layouts.admin')
@section('title', __('cms.read'))
@section('heading', $item['title'] ?? __('news.untitled'))
@section('actions')<a href="{{ route('admin.news.index') }}" class="cms-button">{{ __('cms.back_news') }}</a>@endsection
@section('content')
<article class="cms-card max-w-3xl">
    <dl class="mb-8 grid grid-cols-1 gap-4 border-b border-line pb-6 sm:grid-cols-2">
        <div><dt class="text-xs text-muted">{{ __('cms.source') }}</dt><dd class="mt-1 text-sm font-semibold">{{ $item['source'] ?? __('cms.unknown') }}</dd></div>
        <div><dt class="text-xs text-muted">{{ __('cms.published') }}</dt><dd class="mt-1 text-sm">{{ !empty($item['published_at']) ? \Illuminate\Support\Carbon::parse($item['published_at'])->timezone('Asia/Jakarta')->format('d M Y H:i').' WIB' : __('cms.unknown') }}</dd></div>
    </dl>
    @if(!empty($item['url_to_image']))<img src="{{ $item['url_to_image'] }}" alt="" class="mb-6 max-h-96 w-full object-cover">@endif
    @if(!empty($item['description']))<p class="mb-6 text-lg leading-relaxed">{{ $item['description'] }}</p>@endif
    @if(!empty($item['content']))<p class="whitespace-pre-line text-sm leading-loose text-muted">{{ $item['content'] }}</p>@endif
    @if(!empty($item['url']) && in_array(parse_url($item['url'], PHP_URL_SCHEME), ['http', 'https']))<a href="{{ $item['url'] }}" target="_blank" rel="noopener noreferrer" class="cms-button mt-8">{{ __('cms.original') }}</a>@endif
</article>
@endsection
