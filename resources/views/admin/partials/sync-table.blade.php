<section class="cms-section">
    <div class="cms-panel-header flex flex-wrap items-baseline justify-between gap-2">
        <h2 class="text-lg font-semibold">{{ __('cms.sync') }}</h2>
        <span class="text-xs text-muted">{{ __('admin.jobs.auto_refresh') }}</span>
    </div>
    <div class="cms-table-wrap" role="region" aria-label="{{ __('cms.sync') }}" tabindex="0">
        <table class="cms-table">
            <thead><tr><th>{{ __('admin.sync.th.table') }}</th><th>{{ __('admin.sync.th.last_sync') }}</th><th>{{ __('admin.sync.th.records') }}</th><th>{{ __('admin.sync.th.status') }}</th></tr></thead>
            <tbody>
                <template x-for="row in sync" :key="row.table_name">
                    <tr><th scope="row" x-text="row.table_name"></th><td data-label="{{ __('admin.sync.th.last_sync') }}" x-text="fmt(row.latest_synced_at)"></td><td data-label="{{ __('admin.sync.th.records') }}" class="tabular-nums" x-text="row.records_synced ?? @js(__('cms.unknown'))"></td><td data-label="{{ __('admin.sync.th.status') }}"><span :class="row.status === 'failed' ? 'text-danger' : row.status === 'success' ? 'text-link' : 'text-muted'" x-text="row.status || @js(__('cms.status_unknown'))"></span></td></tr>
                </template>
                <template x-if="!unavailable && sync.length === 0"><tr><td colspan="4" class="text-muted">{{ __('admin.sync.empty') }}</td></tr></template>
            </tbody>
        </table>
    </div>
    <noscript>
        @if($adminUnavailable)<p role="alert" class="mt-4 text-sm text-danger">{{ __('common.empty.admin_unavailable') }}</p>@endif
        <dl class="divide-y divide-line">
            @forelse($sync as $row)
                <div class="flex flex-wrap justify-between gap-3 py-4 text-sm"><dt class="font-semibold">{{ $row['table_name'] }}</dt><dd>{{ $row['status'] ?? __('cms.status_unknown') }} · {{ $row['records_synced'] ?? __('cms.unknown') }}</dd></div>
            @empty @if(!$adminUnavailable)<p class="py-4 text-sm text-muted">{{ __('admin.sync.empty') }}</p>@endif @endforelse
        </dl>
    </noscript>
</section>
