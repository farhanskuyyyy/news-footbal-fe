@extends('layouts.app')

@section('title', __('football.search.title'))

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        {{-- Search form --}}
        <form method="GET" action="{{ route('football.search') }}" class="space-y-3">
            <div class="flex items-center gap-2 rounded-xl border border-line bg-surface p-2 focus-within:border-line">
                <svg viewBox="0 0 24 24" fill="none" class="icon ml-2 h-5 w-5 text-muted"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.8"/><path d="M20 20l-3.5-3.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                <input type="text" name="q" value="{{ $q }}" autofocus placeholder="{{ __('football.search.placeholder') }}"
                       class="flex-1 bg-transparent px-1 py-2 text-sm font-semibold text-white placeholder:text-muted focus:outline-none">
                <button type="submit" class="rounded-xl bg-primary hover:bg-accent px-4 py-2 text-sm font-bold text-white transition-colors">{{ __('football.search.submit') }}</button>
            </div>
            <div class="flex items-center gap-2">
                @foreach(['teams' => __('football.search.teams'), 'players' => __('football.search.players'), 'leagues' => __('football.search.leagues')] as $t => $label)
                    <a href="{{ route('football.search', ['q' => $q, 'type' => $t]) }}"
                       class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-colors {{ $type === $t ? 'bg-primary text-white' : 'bg-surface text-white border border-line hover:text-white' }}">
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
                {!! __('football.search.no_results', ['query' => '<span class="text-white font-bold">'.e($q).'</span>']) !!}
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
                            <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-surface text-lg">{{ $type === 'players' ? \App\Support\Icon::svg('user', 'h-3.5 w-3.5') : \App\Support\Icon::svg('shield', 'h-3.5 w-3.5') }}</div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <h3 class="truncate text-sm font-bold text-white group-hover:text-accent transition-colors">{{ $name }}</h3>
                            <span class="text-xs text-muted capitalize">{{ $type === 'teams' ? __('football.search.teams') : ($type === 'players' ? __('football.search.players') : __('football.search.leagues')) }}</span>
                        </div>
                        <svg viewBox="0 0 24 24" fill="none" class="icon h-4 w-4 text-muted group-hover:text-accent transition-colors"><path d="M9 6l6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
@endsection
