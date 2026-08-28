@extends('layouts.app')

@section('title', ($team['name'] ?? 'Klub') . ' - Profil & Skuad Pemain')

@section('content')
<div class="space-y-8">

    {{-- Breadcrumb --}}
    <div>
        <a href="{{ url()->previous() ?? route('football.index') }}" class="text-xs font-bold text-slate-400 hover:text-emerald-400 transition-colors flex items-center gap-1.5 bg-slate-900 border border-slate-800 px-3.5 py-2 rounded-xl inline-flex">
            &larr; Kembali ke Daftar Klub & Klasemen
        </a>
    </div>

    {{-- Team Header Card --}}
    <div class="bg-gradient-to-br from-slate-900 via-slate-900 to-slate-950 rounded-3xl border border-slate-800 shadow-2xl p-6 sm:p-10 relative overflow-hidden">
        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-8 relative z-10">
            <div class="w-28 h-28 p-4 bg-slate-950 rounded-3xl border border-slate-800 flex items-center justify-center shadow-2xl flex-shrink-0">
                @if(!empty($team['image_path']))
                    <img src="{{ $team['image_path'] }}" alt="{{ $team['name'] }}" class="max-h-full max-w-full object-contain filter drop-shadow-xl">
                @else
                    <span class="text-5xl">🛡️</span>
                @endif
            </div>

            <div class="flex-1 text-center sm:text-left space-y-3">
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-3">
                    <h1 class="text-2xl sm:text-4xl font-black text-white tracking-tight">{{ $team['name'] }}</h1>
                    @if(!empty($team['short_code']))
                        <span class="px-3 py-1 rounded-full text-xs font-mono font-black bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 uppercase tracking-widest">
                            {{ $team['short_code'] }}
                        </span>
                    @endif
                </div>

                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-5 text-xs text-slate-400 font-medium pt-1">
                    @if(!empty($team['founded']))
                        <span class="flex items-center gap-1.5">📅 Berdiri: <strong class="text-slate-200">{{ $team['founded'] }}</strong></span>
                    @endif
                    @if($venue)
                        <span class="flex items-center gap-1.5">📍 Stadion: <strong class="text-slate-200">{{ $venue['name'] }}</strong> ({{ $venue['city_name'] ?? '' }})</span>
                        @if(!empty($venue['capacity']))
                            <span class="flex items-center gap-1.5">👥 Kapasitas: <strong class="text-slate-200">{{ number_format($venue['capacity']) }}</strong></span>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Squad / Players Section --}}
    <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl space-y-6">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <div>
                <h2 class="text-lg font-black text-white flex items-center gap-2">👥 Skuad Pemain</h2>
                <p class="text-xs text-slate-400 mt-0.5">Daftar pemain resmi yang terdaftar di klub ini. Klik pemain untuk melihat detail profil.</p>
            </div>
            <span class="text-xs font-mono font-bold px-3.5 py-1.5 bg-slate-950 text-emerald-400 border border-slate-800 rounded-xl">
                {{ count($players) }} Pemain
            </span>
        </div>

        @if(count($players) > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($players as $p)
                    <a href="{{ route('football.player', $p['id']) }}" class="group p-4 rounded-2xl border border-slate-800 bg-slate-950/80 hover:bg-slate-850 hover:border-emerald-500/50 hover:shadow-xl transition-all flex items-center gap-3.5">
                        @if(!empty($p['image_path']))
                            <img src="{{ $p['image_path'] }}" alt="{{ $p['name'] }}" class="w-12 h-12 rounded-full object-cover border-2 border-slate-700 bg-slate-900 group-hover:border-emerald-400 transition-colors flex-shrink-0">
                        @else
                            <div class="w-12 h-12 rounded-full bg-slate-800 text-slate-300 flex items-center justify-center font-bold text-sm border border-slate-700 flex-shrink-0">
                                👤
                            </div>
                        @endif

                        <div class="min-w-0 flex-1">
                            <h4 class="font-extrabold text-white text-sm truncate group-hover:text-emerald-400 transition-colors">{{ $p['display_name'] ?? $p['name'] }}</h4>
                            <p class="text-[11px] text-slate-400 font-semibold truncate mt-0.5">
                                @if($p['position_id'] == 24) 🧤 Kiper
                                @elseif($p['position_id'] == 25) 🛡️ Bek
                                @elseif($p['position_id'] == 26) ⚙️ Gelandang
                                @elseif($p['position_id'] == 27) ⚡ Penyerang
                                @else ⚽ Pemain
                                @endif
                                @if(!empty($p['date_of_birth']))
                                    • {{ date_diff(date_create($p['date_of_birth']), date_create('today'))->y }} thn
                                @endif
                            </p>
                            @if(!empty($p['height']))
                                <span class="text-[10px] text-slate-500 font-mono block">{{ $p['height'] }} cm / {{ $p['weight'] ?? '-' }} kg</span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="p-12 text-center text-slate-400 text-sm">
                Belum ada data pemain detail pada tim ini.
            </div>
        @endif
    </div>

</div>
@endsection
