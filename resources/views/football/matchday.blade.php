@extends('layouts.app')

@section('title', __('football.matchday.title'))

@php
    $today = date('Y-m-d');
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
                    <span class="kicker mb-3 inline-block text-xs font-bold uppercase text-primary">{{ __('football.matchday.kicker') }}</span>
                    <h1 id="matchday-heading" class="text-2xl font-bold tracking-tight text-white sm:text-3xl">
                        {{ $date === $today ? __('football.matchday.today') : \Illuminate\Support\Carbon::parse($date)->locale(app()->getLocale())->translatedFormat('l, d F Y') }}
                    </h1>
                    <p id="matchday-count" class="mt-1.5 font-mono text-xs text-body sm:text-sm">{{ trans_choice('football.matchday.count', count($fixtures), ['count' => count($fixtures)]) }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('football.matchday', ['date' => $prev]) }}" data-matchday-nav="{{ $prev }}"
                       class="flex h-10 w-10 items-center justify-center rounded-lg border border-line text-white transition-colors hover:text-accent">
                        <x-icon name="arrow-left" class="h-4 w-4" />
                    </a>
                    <form method="GET" action="{{ route('football.matchday') }}">
                        <input type="date" name="date" id="matchday-date" value="{{ $date }}"
                               class="rounded-lg border border-line bg-ink px-3 py-2 font-mono text-sm font-bold text-white focus:outline-none focus:ring-2 focus:ring-primary">
                        <noscript><button type="submit" class="ml-2 rounded-lg border border-line px-3 py-2 text-xs text-white">{{ __('common.actions.search') }}</button></noscript>
                    </form>
                    <a href="{{ route('football.matchday', ['date' => $next]) }}" data-matchday-nav="{{ $next }}"
                       class="flex h-10 w-10 items-center justify-center rounded-lg border border-line text-white transition-colors hover:text-accent">
                        <x-icon name="arrow-right" class="h-4 w-4" />
                    </a>
                </div>
            </div>
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
            let busy = false;

            function shift(date, days) {
                const d = new Date(date + 'T00:00:00');
                d.setDate(d.getDate() + days);
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
                if (busy || !date) return;
                busy = true;
                root.setAttribute('aria-busy', 'true');
                try {
                    const res = await fetch(endpoint + '?date=' + encodeURIComponent(date), {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    if (!res.ok) throw new Error(res.status);
                    const html = await res.text();

                    const list = document.getElementById('matchday-list');
                    const wrapper = document.createElement('div');
                    wrapper.innerHTML = html;
                    const fresh = wrapper.querySelector('#matchday-list');
                    if (!fresh || !list) throw new Error('unexpected response');

                    list.replaceWith(fresh);
                    if (heading) heading.textContent = fresh.dataset.heading;
                    if (count) count.textContent = fresh.dataset.count;
                    syncControls(date);
                    if (push) history.pushState({ date: date }, '', endpoint + '?date=' + date);
                } catch (e) {
                    // Fall back to a normal navigation rather than leaving the
                    // page showing the wrong date.
                    window.location = endpoint + '?date=' + date;
                } finally {
                    busy = false;
                    root.removeAttribute('aria-busy');
                }
            }

            root.addEventListener('click', (e) => {
                const link = e.target.closest('[data-matchday-nav]');
                if (!link) return;
                e.preventDefault();
                load(link.dataset.matchdayNav, true);
            });

            if (picker) {
                picker.form.addEventListener('submit', (e) => e.preventDefault());
                picker.addEventListener('change', () => load(picker.value, true));
            }

            window.addEventListener('popstate', () => {
                const date = new URLSearchParams(window.location.search).get('date');
                load(date || new Date().toISOString().slice(0, 10), false);
            });
        })();
    </script>
@endsection
