@if ($paginator->hasPages())
    {{-- Restyled to the site palette: flat surfaces, bordered pills, no shadow. --}}
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between gap-4">
        <div class="flex flex-1 items-center justify-between">
            <p class="text-xs text-body">
                {!! __('Showing') !!}
                <span class="font-medium text-white">{{ $paginator->firstItem() }}</span>
                {!! __('to') !!}
                <span class="font-medium text-white">{{ $paginator->lastItem() }}</span>
                {!! __('of') !!}
                <span class="font-medium text-white">{{ $paginator->total() }}</span>
                {!! __('results') !!}
            </p>

            <div class="flex items-center gap-1.5">
                @if ($paginator->onFirstPage())
                    <span aria-disabled="true" class="rounded-lg border border-line px-3 py-1.5 text-xs text-muted">
                        {!! __('pagination.previous') !!}
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                       class="rounded-lg border border-line px-3 py-1.5 text-xs text-body transition-colors hover:text-white">
                        {!! __('pagination.previous') !!}
                    </a>
                @endif

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span aria-disabled="true" class="px-2 text-xs text-muted">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page" class="rounded-lg bg-primary px-3 py-1.5 text-xs font-semibold text-white">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}"
                                   class="rounded-lg border border-line px-3 py-1.5 text-xs text-body transition-colors hover:text-white">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                       class="rounded-lg border border-line px-3 py-1.5 text-xs text-body transition-colors hover:text-white">
                        {!! __('pagination.next') !!}
                    </a>
                @else
                    <span aria-disabled="true" class="rounded-lg border border-line px-3 py-1.5 text-xs text-muted">
                        {!! __('pagination.next') !!}
                    </span>
                @endif
            </div>
        </div>
    </nav>
@endif
