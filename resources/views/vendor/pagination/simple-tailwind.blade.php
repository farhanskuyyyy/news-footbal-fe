@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between gap-2">
        @if ($paginator->onFirstPage())
            <span class="rounded-lg border border-line px-3 py-1.5 text-xs text-muted">{!! __('pagination.previous') !!}</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
               class="rounded-lg border border-line px-3 py-1.5 text-xs text-body transition-colors hover:text-white">{!! __('pagination.previous') !!}</a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next"
               class="rounded-lg border border-line px-3 py-1.5 text-xs text-body transition-colors hover:text-white">{!! __('pagination.next') !!}</a>
        @else
            <span class="rounded-lg border border-line px-3 py-1.5 text-xs text-muted">{!! __('pagination.next') !!}</span>
        @endif
    </nav>
@endif
