{{-- The part of the matchday page that changes when the date changes. Rendered
     standalone for the AJAX swap, and included by the full page. --}}
@php
    $today = date('Y-m-d');
    // $fixtures already arrives with CMS-enabled leagues (status = true) first,
    // and groupBy keeps that order, so those leagues stay at the top.
    $byLeague = collect($fixtures)->groupBy(fn ($f) => $f['league']['name'] ?? '—');
    $enabledIds = collect($enabledLeagueIds ?? []);
@endphp

<div class="space-y-8" id="matchday-list"
     data-date="{{ $date }}"
     data-heading="{{ $date === $today ? __('football.matchday.today') : \Illuminate\Support\Carbon::parse($date)->locale(app()->getLocale())->translatedFormat('l, d F Y') }}"
     data-count="{{ trans_choice('football.matchday.count', count($fixtures), ['count' => count($fixtures)]) }}">
    @if(count($fixtures) > 0)
        @foreach($byLeague as $leagueName => $leagueFixtures)
            @php
                $leagueId = (int) ($leagueFixtures[0]['league_id'] ?? $leagueFixtures[0]['league']['id'] ?? 0);
            @endphp
            <div class="space-y-3">
                <h2 class="flex items-center gap-2 text-sm font-bold text-white">
                    {{-- The header opens the league's own portal page. --}}
                    <a href="{{ $leagueId ? route('football.index', ['league_id' => $leagueId]) : '#' }}"
                       class="group flex items-center gap-2 transition-colors hover:text-accent">
                        @if(!empty($leagueFixtures[0]['league']['image_path']))
                            <img src="{{ $leagueFixtures[0]['league']['image_path'] }}" alt="" class="h-5 w-5 object-contain">
                        @endif
                        <span>{{ $leagueName }}</span>
                        <x-icon name="arrow-right" class="h-3.5 w-3.5 shrink-0 text-muted transition-colors group-hover:text-accent" />
                    </a>
                    <span class="font-mono text-xs font-bold text-muted">({{ count($leagueFixtures) }})</span>
                    @if($enabledIds->contains($leagueId))
                        <span class="rounded-lg border border-line px-1.5 py-0.5 text-xs font-bold uppercase tracking-wider text-accent">{{ __('football.matchday.main_league') }}</span>
                    @endif
                </h2>
                <div class="grid gap-4 md:grid-cols-2">
                    @foreach($leagueFixtures as $f)
                        @include('football.partials.live-card', ['f' => $f])
                    @endforeach
                </div>
            </div>
        @endforeach
    @else
        <div class="rounded-xl border border-dashed border-line bg-surface p-12 text-center">
            <p class="text-base font-bold text-white">{{ __('football.matchday.empty') }}</p>
            <p class="mt-1 text-xs text-muted">{{ __('football.matchday.empty_hint') }}</p>
        </div>
    @endif
</div>
