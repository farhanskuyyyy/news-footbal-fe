@extends('layouts.app')

@section('title', __('football.search.title'))

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        {{-- Search form --}}
        <form method="GET" action="{{ route('football.search') }}" class="space-y-3">
            <div class="flex flex-wrap items-center gap-2 rounded-xl border border-control bg-surface p-2">
                <svg viewBox="0 0 24 24" fill="none" class="icon ml-2 h-5 w-5 text-muted"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.8"/><path d="M20 20l-3.5-3.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                <input type="text" name="q" value="{{ $q }}" autofocus placeholder="{{ __('football.search.placeholder') }}"
                       aria-label="{{ __('common.nav.search_title') }}"
                       class="flex-1 bg-transparent px-1 py-2 text-sm font-semibold text-foreground placeholder:text-muted focus:outline-none">
                <button type="submit" class="rounded-lg bg-primary hover:bg-primary-hover px-4 py-2 text-sm font-bold text-on-primary transition-colors">{{ __('football.search.submit') }}</button>
            </div>
            <div class="flex items-center gap-2">
                @foreach(['teams' => __('football.search.teams'), 'players' => __('football.search.players'), 'leagues' => __('football.search.leagues')] as $t => $label)
                    <a href="{{ route('football.search', ['q' => $q, 'type' => $t]) }}"
                       class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-colors {{ $type === $t ? 'bg-primary text-on-primary' : 'bg-surface text-foreground border border-line hover:text-foreground' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </form>

        {{-- Results --}}
        @if(strlen($q) < 2)
            <p class="rounded-xl border border-dashed border-line bg-surface p-8 text-center text-sm text-muted">
                {{ __('football.search.min_chars') }}
            </p>
        @elseif(count($results) === 0)
            <p class="rounded-xl border border-dashed border-line bg-surface p-8 text-center text-sm text-body">
                {!! __('football.search.no_results', ['query' => '<span class="text-foreground font-bold">'.e($q).'</span>']) !!}
            </p>
        @else
            <div class="space-y-2">
                @foreach($results as $r)
                    @php
                        $href = match($type) {
                            'teams' => route('football.team', $r['id']),
                            'players' => route('football.player', $r['id']),
                            'leagues' => route('football.index', ['league_id' => $r['id']]),
                            default => '#',
                        };
                        $name = $r['display_name'] ?? $r['name'] ?? __('football.search.unnamed');
                    @endphp
                    <a href="{{ $href }}" class="group flex items-center gap-4 rounded-xl border border-line bg-surface p-3.5 transition-all hover:border-line hover:bg-surface">
                        @if(!empty($r['image_path']))
                            <img src="{{ $r['image_path'] }}" alt="" class="h-11 w-11 rounded-lg object-contain bg-ink p-1 border border-line">
                        @else
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-canvas text-muted"><x-icon :name="$type === 'players' ? 'user' : 'shield'" class="h-5 w-5" /></div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <h3 class="truncate text-sm font-bold text-foreground group-hover:text-link transition-colors">{{ $name }}</h3>
                            <span class="text-xs text-muted capitalize">{{ $type === 'teams' ? __('football.search.teams') : ($type === 'players' ? __('football.search.players') : __('football.search.leagues')) }}</span>
                        </div>
                        <svg viewBox="0 0 24 24" fill="none" class="icon h-4 w-4 text-muted group-hover:text-link transition-colors"><path d="M9 6l6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
@endsection
