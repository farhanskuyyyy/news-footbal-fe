@extends('layouts.app')

@section('title', __('football.loading.title'))

@section('content')
    <div class="mx-auto max-w-lg py-16 text-center"
         x-data="fixtureLoader({{ $fixtureId }})" x-init="start()">

        {{-- Spinner --}}
        <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center">
            <svg viewBox="0 0 24 24" fill="none" class="icon h-16 w-16 animate-spin text-accent">
                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" opacity="0.2"/>
                <path d="M21 12a9 9 0 00-9-9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </div>

        <template x-if="!failed">
            <div>
                <span class="kicker block text-xs font-bold uppercase text-primary mb-2">{{ __('football.loading.kicker') }}</span>
                <h1 class="text-2xl font-bold text-white">{{ __('football.loading.heading') }}</h1>
                <p class="mt-2 text-sm text-body" x-text="statusText">{{ __('football.loading.status_start') }}</p>
            </div>
        </template>

        <template x-if="failed">
            <div>
                <h1 class="text-2xl font-bold text-white">{{ __('football.loading.failed_heading') }}</h1>
                <p class="mt-2 text-sm text-body">{{ __('football.loading.failed_text') }}</p>
                <div class="mt-6 flex items-center justify-center gap-3">
                    <button @click="failed=false; start()" class="rounded-xl bg-primary hover:bg-accent px-4 py-2.5 text-sm font-bold text-white transition-colors">{{ __('football.loading.retry') }}</button>
                    <a href="{{ route('football.index') }}" class="rounded-xl border border-line bg-surface px-4 py-2.5 text-sm font-bold text-white hover:text-white transition-colors">{{ __('football.loading.back') }}</a>
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
