@props(['variant' => 'matches'])

<div {{ $attributes->merge(['class' => 'space-y-4']) }} aria-hidden="true">
    @if($variant === 'fixture')
        <div class="rounded-xl border border-line bg-surface p-6 sm:p-10">
            <span class="skeleton-block mx-auto mb-8 h-4 w-32"></span>
            <div class="grid grid-cols-3 items-center gap-4">
                @foreach(range(1, 3) as $column)
                    <div class="space-y-4">
                        <span class="skeleton-block mx-auto h-12 w-12 sm:h-16 sm:w-16"></span>
                        <span class="skeleton-block mx-auto h-4 w-3/4"></span>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="flex gap-6 border-b border-line p-4">
            @foreach(range(1, 3) as $tab)
                <span class="skeleton-block h-4 w-20"></span>
            @endforeach
        </div>
        <div class="rounded-xl border border-line bg-surface p-6">
            @foreach(range(1, 4) as $row)
                <div class="flex items-center justify-between gap-6 py-4">
                    <span class="skeleton-block h-4 w-12"></span>
                    <span class="skeleton-block h-4 w-1/3"></span>
                    <span class="skeleton-block h-4 w-12"></span>
                </div>
            @endforeach
        </div>
    @else
        <div class="rounded-xl border border-line bg-surface">
            <div class="flex items-center gap-3 border-b border-line p-4">
                <span class="skeleton-block h-6 w-6"></span>
                <span class="skeleton-block h-4 w-40"></span>
            </div>
            @foreach(range(1, 5) as $row)
                <div class="match-row border-b border-line p-4 last:border-0">
                    <span class="skeleton-block h-4 w-12"></span>
                    <div class="space-y-3">
                        <span class="skeleton-block h-4 w-3/4 max-w-64"></span>
                        <span class="skeleton-block h-4 w-1/2 max-w-48"></span>
                    </div>
                    <div class="space-y-3">
                        <span class="skeleton-block h-4 w-6"></span>
                        <span class="skeleton-block h-4 w-6"></span>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
