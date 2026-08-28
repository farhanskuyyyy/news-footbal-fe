@extends('layouts.app')

@section('title', 'Cari - KREASIBALL')

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        {{-- Search form --}}
        <form method="GET" action="{{ route('football.search') }}" class="space-y-3">
            <div class="flex items-center gap-2 rounded-2xl border border-slate-800 bg-slate-900/80 p-2 shadow-xl focus-within:border-emerald-500/60">
                <svg viewBox="0 0 24 24" fill="none" class="ml-2 h-5 w-5 text-slate-500"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.8"/><path d="M20 20l-3.5-3.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                <input type="text" name="q" value="{{ $q }}" autofocus placeholder="Cari klub, pemain, atau liga..."
                       class="flex-1 bg-transparent px-1 py-2 text-sm font-semibold text-white placeholder:text-slate-500 focus:outline-none">
                <button type="submit" class="rounded-xl bg-emerald-500 hover:bg-emerald-400 px-4 py-2 text-sm font-bold text-slate-950 transition-colors">Cari</button>
            </div>
            <div class="flex items-center gap-2">
                @foreach(['teams' => 'Klub', 'players' => 'Pemain', 'leagues' => 'Liga'] as $t => $label)
                    <a href="{{ route('football.search', ['q' => $q, 'type' => $t]) }}"
                       class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-colors {{ $type === $t ? 'bg-emerald-500 text-slate-950' : 'bg-slate-900 text-slate-400 border border-slate-800 hover:text-slate-200' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </form>

        {{-- Results --}}
        @if(strlen($q) < 2)
            <p class="rounded-2xl border border-dashed border-slate-800 bg-slate-900/40 p-8 text-center text-sm text-slate-500">
                Ketik minimal 2 huruf untuk mulai mencari.
            </p>
        @elseif(count($results) === 0)
            <p class="rounded-2xl border border-dashed border-slate-800 bg-slate-900/40 p-8 text-center text-sm text-slate-400">
                Tidak ada hasil untuk "<span class="text-white font-bold">{{ $q }}</span>".
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
                        $name = $r['display_name'] ?? $r['name'] ?? 'Tanpa nama';
                    @endphp
                    <a href="{{ $href }}" class="group flex items-center gap-4 rounded-2xl border border-slate-800 bg-slate-900/80 p-3.5 transition-all hover:border-emerald-500/50 hover:bg-slate-900">
                        @if(!empty($r['image_path']))
                            <img src="{{ $r['image_path'] }}" alt="" class="h-11 w-11 rounded-lg object-contain bg-slate-950 p-1 border border-slate-800">
                        @else
                            <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-slate-800 text-lg">{{ $type === 'players' ? '👤' : '🛡️' }}</div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <h3 class="truncate text-sm font-bold text-white group-hover:text-emerald-400 transition-colors">{{ $name }}</h3>
                            <span class="text-xs text-slate-500 capitalize">{{ $type === 'teams' ? 'Klub' : ($type === 'players' ? 'Pemain' : 'Liga') }}</span>
                        </div>
                        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 text-slate-600 group-hover:text-emerald-400 transition-colors"><path d="M9 6l6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
@endsection
