<section class="cms-section">
    <div class="cms-panel-header flex flex-wrap items-baseline justify-between gap-2">
        <h2 class="text-lg font-semibold">{{ __('admin.jobs.heading') }}</h2>
        <span class="text-xs text-muted">{{ __('admin.jobs.auto_refresh') }}</span>
    </div>
    <p x-show="unavailable" x-cloak role="alert" class="mb-4 text-sm text-danger">{{ __('common.empty.admin_unavailable') }}</p>
    <p x-show="!unavailable && running.length === 0" x-cloak class="text-sm text-muted">{{ __('admin.jobs.empty') }}</p>
    <noscript>
        @if($adminUnavailable)<p role="alert" class="text-sm text-danger">{{ __('common.empty.admin_unavailable') }}</p>
        @elseif(!count($running))<p class="text-sm text-muted">{{ __('admin.jobs.empty') }}</p>@endif
        @foreach($running as $job)<form method="POST" action="{{ route('admin.scrape.stop', $job) }}" class="my-3 flex items-center justify-between gap-3">@csrf<span class="text-sm">{{ $job }}</span><button class="cms-button text-danger">{{ __('admin.jobs.stop') }}</button></form>@endforeach
    </noscript>
    <div class="divide-y divide-line">
        <template x-for="job in running" :key="job">
            <div class="flex flex-wrap items-center justify-between gap-3 py-3">
                <span class="text-sm font-semibold" x-text="job"></span>
                <form method="POST" :action="`{{ url('/admin/scrape/stop') }}/${encodeURIComponent(job)}`">
                    @csrf<button class="cms-button text-danger">{{ __('admin.jobs.stop') }}</button>
                </form>
            </div>
        </template>
    </div>
</section>
