@extends('layouts.app')

@section('title', ($player['display_name'] ?? $player['name'] ?? 'Profil Pemain') . ' - KREASIBALL')

@section('content')
<div class="space-y-8">

    {{-- Breadcrumb & Back --}}
    <div class="flex items-center justify-between">
        <a href="{{ url()->previous() ?? route('football.index') }}" class="text-xs font-bold text-slate-400 hover:text-emerald-400 transition-colors flex items-center gap-1.5 bg-slate-900 border border-slate-800 px-3.5 py-2 rounded-xl">
            &larr; Kembali
        </a>
        <span class="text-xs text-slate-500 font-mono font-bold">ID: #{{ $player['id'] ?? '-' }}</span>
    </div>

    {{-- PLAYER HERO BANNER --}}
    <div class="bg-gradient-to-br from-slate-900 via-slate-900 to-slate-950 rounded-3xl p-6 sm:p-10 border border-slate-800 shadow-2xl relative overflow-hidden">
        {{-- Background Glow --}}
        <div class="absolute -right-20 -top-20 w-80 h-80 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-8 relative z-10">
            {{-- Player Photo --}}
            <div class="w-32 h-32 sm:w-40 sm:h-40 rounded-3xl bg-slate-950 border-2 border-emerald-500/40 p-1 flex-shrink-0 shadow-2xl overflow-hidden flex items-center justify-center">
                @if(!empty($player['image_path']))
                    <img src="{{ $player['image_path'] }}" alt="{{ $player['name'] }}" class="w-full h-full object-cover rounded-2xl">
                @else
                    <span class="text-6xl">👤</span>
                @endif
            </div>

            {{-- Player Info --}}
            <div class="flex-1 text-center sm:text-left space-y-4">
                <div>
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mb-2">
                        @if(!empty($player['position_id']))
                            <span class="px-3 py-1 rounded-full text-xs font-black bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 uppercase tracking-wider font-mono">
                                Posisi #{{ $player['position_id'] }}
                            </span>
                        @endif
                        @if($nationality)
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-800 text-slate-300 border border-slate-700">
                                🌍 {{ $nationality['name'] }}
                            </span>
                        @endif
                    </div>
                    <h1 class="text-2xl sm:text-4xl font-black tracking-tight text-white">
                        {{ $player['display_name'] ?? $player['name'] ?? 'Nama Pemain' }}
                    </h1>
                    @if(!empty($player['common_name']) && $player['common_name'] !== $player['name'])
                        <p class="text-sm text-slate-400 font-medium mt-1">Nama Lengkap: {{ $player['name'] }}</p>
                    @endif
                </div>

                {{-- Player Metrics Grid --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2">
                    <div class="bg-slate-950/80 border border-slate-800/80 p-3 rounded-2xl">
                        <span class="text-[10px] uppercase font-bold text-slate-500 block">Kewarganegaraan</span>
                        <span class="text-xs sm:text-sm font-extrabold text-white">{{ $country['name'] ?? $nationality['name'] ?? '-' }}</span>
                    </div>
                    <div class="bg-slate-950/80 border border-slate-800/80 p-3 rounded-2xl">
                        <span class="text-[10px] uppercase font-bold text-slate-500 block">Tinggi Badan</span>
                        <span class="text-xs sm:text-sm font-extrabold text-white font-mono">{{ $player['height'] ? $player['height'] . ' cm' : '-' }}</span>
                    </div>
                    <div class="bg-slate-950/80 border border-slate-800/80 p-3 rounded-2xl">
                        <span class="text-[10px] uppercase font-bold text-slate-500 block">Berat Badan</span>
                        <span class="text-xs sm:text-sm font-extrabold text-white font-mono">{{ $player['weight'] ? $player['weight'] . ' kg' : '-' }}</span>
                    </div>
                    <div class="bg-slate-950/80 border border-slate-800/80 p-3 rounded-2xl">
                        <span class="text-[10px] uppercase font-bold text-slate-500 block">Tanggal Lahir</span>
                        <span class="text-xs sm:text-sm font-extrabold text-white font-mono">
                            {{ $player['date_of_birth'] ? date('d M Y', strtotime($player['date_of_birth'])) : '-' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- PLAYER DETAILS CONTENT GRID --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

        {{-- CURRENT SQUADS & CLUBS --}}
        <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
            <h3 class="text-base font-black text-white flex items-center gap-2">
                🛡️ Klub & Tim yang Dibela
            </h3>

            @if(count($teams) > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($teams as $t)
                        <a href="{{ route('football.team', $t['id']) }}" class="group bg-slate-950 p-4 rounded-2xl border border-slate-800 hover:border-emerald-500/50 flex items-center gap-3.5 transition-all">
                            @if(!empty($t['image_path']))
                                <img src="{{ $t['image_path'] }}" alt="{{ $t['name'] }}" class="w-10 h-10 object-contain filter drop-shadow">
                            @else
                                <div class="w-10 h-10 rounded-xl bg-slate-800 flex items-center justify-center text-sm">🛡️</div>
                            @endif
                            <div class="min-w-0">
                                <h4 class="font-bold text-sm text-white group-hover:text-emerald-400 transition-colors truncate">{{ $t['name'] }}</h4>
                                <span class="text-xs text-slate-500">Lihat Profil Klub &rarr;</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <p class="text-slate-400 text-xs py-4">Belum ada asosiasi klub aktif.</p>
            @endif
        </div>

        {{-- TOPSCORER RECORDS --}}
        <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
            <h3 class="text-base font-black text-white flex items-center gap-2">
                🏆 Catatan Gol Musim
            </h3>

            @if(count($topscorers) > 0)
                <div class="space-y-2.5">
                    @foreach($topscorers as $ts)
                        <div class="bg-slate-950 p-3.5 rounded-2xl border border-slate-800 flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-300">Musim #{{ $ts['season_id'] ?? '-' }}</span>
                            <div class="flex items-center gap-4">
                                <span class="text-xs font-semibold text-slate-400">Peringkat: <strong class="text-white font-mono">#{{ $ts['position'] }}</strong></span>
                                <span class="px-3 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black text-xs border border-emerald-500/30">
                                    ⚽ {{ $ts['total'] }} Gol
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-slate-400 text-xs py-4">Belum ada catatan top skor yang tercatat di database.</p>
            @endif
        </div>

        {{-- TRANSFER HISTORY TIMELINE --}}
        @if(count($transfers) > 0)
            <div class="lg:col-span-2 bg-slate-900/90 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
                <h3 class="text-base font-black text-white flex items-center gap-2">
                    💸 Riwayat Transfer Pemain
                </h3>

                <div class="space-y-3">
                    @foreach($transfers as $tr)
                        <div class="bg-slate-950 p-4 rounded-2xl border border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <span class="text-xs font-bold text-emerald-400 block">{{ $tr['type'] ?? 'Transfer' }}</span>
                                <span class="text-xs text-slate-400 font-medium">Tanggal: {{ $tr['date'] ? date('d F Y', strtotime($tr['date'])) : 'Resmi' }}</span>
                            </div>
                            <div class="flex items-center gap-3 text-xs font-bold">
                                <span class="bg-slate-900 px-3 py-1.5 rounded-xl text-slate-300 border border-slate-800">
                                    {{ $tr['from_team']['name'] ?? 'Klub Asal' }}
                                </span>
                                <span class="text-emerald-400 font-mono font-black">&rarr;</span>
                                <span class="bg-emerald-950/60 px-3 py-1.5 rounded-xl text-emerald-300 border border-emerald-800/50">
                                    {{ $tr['to_team']['name'] ?? 'Klub Tujuan' }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>

</div>
@endsection
