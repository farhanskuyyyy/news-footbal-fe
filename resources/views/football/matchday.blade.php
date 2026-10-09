@extends('layouts.app')

@section('title', __('football.matchday.title'))

@php
    $today = now('Asia/Jakarta')->format('Y-m-d');
    $prev = date('Y-m-d', strtotime($date.' -1 day'));
    $next = date('Y-m-d', strtotime($date.' +1 day'));
@endphp

@section('content')
    {{-- The date controls swap the list in place; every control is still a real
         link or form, so the page works unchanged without JavaScript. --}}
    <div class="space-y-8" id="matchday" data-endpoint="{{ route('football.matchday') }}">
        <div class="rounded-xl border border-line p-6 sm:p-8">
            <div class="flex flex-col justify-between gap-6 sm:flex-row sm:items-end">
                <div>
                    <span class="kicker mb-3 inline-block text-xs font-bold uppercase text-link">{{ __('football.matchday.kicker') }}</span>
                    <h1 id="matchday-heading" class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                        {{ $date === $today ? __('football.matchday.today') : \Illuminate\Support\Carbon::parse($date)->locale(app()->getLocale())->translatedFormat('l, d F Y') }}
                    </h1>
                    <p id="matchday-count" role="status" class="mt-1.5 font-mono text-xs text-body sm:text-sm">{{ ($footballUnavailable ?? false) && !count($fixtures) ? '—' : trans_choice('football.matchday.count', count($fixtures), ['count' => count($fixtures)]) }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('football.matchday', ['date' => $prev]) }}" data-matchday-nav="{{ $prev }}"
                       aria-label="{{ __('common.actions.previous_day') }}"
                       class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg border border-control text-foreground transition-colors hover:text-link">
                        <x-icon name="arrow-left" class="h-4 w-4" />
                    </a>
                    <form method="GET" action="{{ route('football.matchday') }}">
                        <input type="date" name="date" id="matchday-date" value="{{ $date }}"
                               aria-label="{{ __('common.nav.schedule') }}"
                               class="rounded-lg border border-line bg-ink px-3 py-2 font-mono text-sm font-bold text-foreground focus:outline-none focus:ring-2 focus:ring-primary">
                        <noscript><button type="submit" class="ml-2 rounded-lg border border-line px-3 py-2 text-xs text-foreground">{{ __('common.actions.search') }}</button></noscript>
                    </form>
                    <a href="{{ route('football.matchday', ['date' => $next]) }}" data-matchday-nav="{{ $next }}"
                       aria-label="{{ __('common.actions.next_day') }}"
                       class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg border border-control text-foreground transition-colors hover:text-link">
                        <x-icon name="arrow-right" class="h-4 w-4" />
                    </a>
                </div>
            </div>
        </div>

        <div id="matchday-skeleton" hidden>
            <p role="status" class="mb-4 text-sm text-body">{{ __('common.loading_matches') }}</p>
            <x-skeleton />
        </div>
        @include('football.partials.matchday-list', ['fixtures' => $fixtures, 'date' => $date, 'enabledLeagueIds' => $enabledLeagueIds])
    </div>

    <script>
        (function () {
            const root = document.getElementById('matchday');
            if (!root || !window.fetch) return;

            const endpoint = root.dataset.endpoint;
            const heading = document.getElementById('matchday-heading');
            const count = document.getElementById('matchday-count');
            const picker = document.getElementById('matchday-date');
            let activeRequest;

            function shift(date, days) {
                const d = new Date(date + 'T00:00:00Z');
                d.setUTCDate(d.getUTCDate() + days);
                return d.toISOString().slice(0, 10);
            }

            // Re-point the arrows after a swap so they keep stepping one day.
            function syncControls(date) {
                if (picker) picker.value = date;
                root.querySelectorAll('[data-matchday-nav]').forEach((a, i) => {
                    const target = shift(date, i === 0 ? -1 : 1);
                    a.dataset.matchdayNav = target;
                    a.href = endpoint + '?date=' + target;
                });
            }

            async function load(date, push) {
                if (!date) return;
                activeRequest?.abort();
                const controller = new AbortController();
                activeRequest = controller;
                root.setAttribute('aria-busy', 'true');
                document.getElementById('matchday-list').hidden = true;
                document.getElementById('matchday-skeleton').hidden = false;
                try {
                    const res = await fetch(endpoint + '?date=' + encodeURIComponent(date), {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        signal: controller.signal,
                    });
                    if (!res.ok) throw new Error(res.status);
                    const html = await res.text();

                    const list = document.getElementById('matchday-list');
                    const wrapper = document.createElement('div');
                    wrapper.innerHTML = html;
                    const fresh = wrapper.querySelector('#matchday-list');
                    if (!fresh || !list) throw new Error('unexpected response');

                    list.replaceWith(fresh);
                    document.dispatchEvent(new Event('pitchticker:matches'));
                    if (heading) heading.textContent = fresh.dataset.heading;
                    if (count) count.textContent = fresh.dataset.count;
                    syncControls(date);
                    if (push) history.pushState({ date: date }, '', endpoint + '?date=' + date);
                } catch (e) {
                    if (e.name === 'AbortError') return;
                    // Fall back to a normal navigation rather than leaving the
                    // page showing the wrong date.
                    window.location = endpoint + '?date=' + date;
                } finally {
                    if (activeRequest === controller) {
                        root.removeAttribute('aria-busy');
                        document.getElementById('matchday-skeleton').hidden = true;
                        document.getElementById('matchday-list').hidden = false;
                    }
                }
            }

            root.addEventListener('click', (e) => {
                const link = e.target.closest('[data-matchday-nav]');
                if (!link || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey || e.button !== 0) return;
                e.preventDefault();
                load(link.dataset.matchdayNav, true);
            });

            if (picker) {
                picker.form.addEventListener('submit', (e) => e.preventDefault());
                picker.addEventListener('change', () => load(picker.value, true));
            }

            window.addEventListener('popstate', () => {
                const date = new URLSearchParams(window.location.search).get('date');
                load(date || @json($today), false);
            });
        })();
    </script>
@endsection
