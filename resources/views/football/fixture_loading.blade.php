@extends('layouts.app')

@section('title', __('football.loading.title'))

@section('content')
    <div class="space-y-6"
         x-data="fixtureLoader({{ $fixtureId }})" x-init="start()">

        <template x-if="!failed">
            <div class="text-center">
                <span class="kicker block text-xs font-bold uppercase text-link mb-2">{{ __('football.loading.kicker') }}</span>
                <h1 class="text-2xl font-bold text-foreground">{{ __('football.loading.heading') }}</h1>
                <p role="status" class="mt-2 text-sm text-body" x-text="statusText">{{ __('football.loading.status_start') }}</p>
            </div>
        </template>

        <div x-show="!failed" :aria-busy="!failed" id="fixture-skeleton">
            <x-skeleton variant="fixture" />
        </div>

        <template x-if="failed">
            <div class="mx-auto max-w-lg py-16 text-center">
                <h1 class="text-2xl font-bold text-foreground">{{ __('football.loading.failed_heading') }}</h1>
                <p class="mt-2 text-sm text-body">{{ __('football.loading.failed_text') }}</p>
                <div class="mt-6 flex items-center justify-center gap-3">
                    <button @click="failed=false; start()" class="rounded-xl bg-primary hover:bg-primary-hover px-4 py-2.5 text-sm font-bold text-on-primary transition-colors">{{ __('football.loading.retry') }}</button>
                    <a href="{{ route('football.index') }}" class="rounded-xl border border-line bg-surface px-4 py-2.5 text-sm font-bold text-foreground hover:text-foreground transition-colors">{{ __('football.loading.back') }}</a>
                </div>
            </div>
        </template>
    </div>

    <script>
        function fixtureLoader(fixtureId) {
            return {
                failed: false,
                statusText: @json(__('football.loading.status_start')),
                async start() {
                    this.failed = false;
                    this.statusText = @json(__('football.loading.status_downloading'));
                    try {
                        const res = await fetch(`{{ url('/football/fixtures') }}/${fixtureId}/prepare`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                        });
                        if (!res.ok) throw new Error(res.status);
                        const data = await res.json();
                        if (data.ready) {
                            this.statusText = @json(__('football.loading.status_done'));
                            window.location = `{{ url('/football/fixtures') }}/${fixtureId}`;
                            return;
                        }
                    } catch (e) { /* fallthrough to failed */ }
                    this.failed = true;
                },
            }
        }
    </script>
@endsection
