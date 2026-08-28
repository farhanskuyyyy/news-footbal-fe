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
            @php
                // Single source for citizenship + flag so the badge and the metric
                // box never disagree. Nationality (national team) takes priority,
                // country of birth is the fallback.
                $natCountry = $nationality ?: $country;
            @endphp
            <div class="flex-1 text-center sm:text-left space-y-4">
                <div>
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mb-2">
                        @if(!empty($position) || !empty($player['position_id']))
                            <span class="px-3 py-1 rounded-full text-xs font-black bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 uppercase tracking-wider">
                                {{ $position ?: 'Posisi #'.$player['position_id'] }}
                            </span>
                        @endif
                        @if(!empty($detailedPosition) && $detailedPosition !== $position)
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-800 text-slate-300 border border-slate-700">
                                {{ $detailedPosition }}
                            </span>
                        @endif
                        @if($natCountry)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-800 text-slate-300 border border-slate-700">
                                @if(!empty($natCountry['image_path']))
                                    <img src="{{ $natCountry['image_path'] }}" alt="{{ $natCountry['name'] }}" class="w-4 h-4 rounded-sm object-cover">
                                @else
                                    🌍
                                @endif
                                {{ $natCountry['name'] }}
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
                        <span class="flex items-center gap-1.5 text-xs sm:text-sm font-extrabold text-white">
                            @if(!empty($natCountry['image_path']))
                                <img src="{{ $natCountry['image_path'] }}" alt="" class="w-4 h-4 rounded-sm object-cover">
                            @endif
                            {{ $natCountry['name'] ?? '-' }}
                        </span>
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

        {{-- CLUB HISTORY (per musim, dari squad membership) --}}
        <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400">
                    <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5"><path d="M12 3l7 3v5c0 4-3 6.5-7 8-4-1.5-7-4-7-8V6l7-3z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
                </span>
                <div>
                    <span class="kicker block text-[10px] font-bold uppercase text-emerald-400">Karier Klub</span>
                    <h3 class="text-base font-black text-white">Klub per Musim</h3>
                </div>
            </div>

            @if(!empty($clubHistory))
                <div class="space-y-2.5">
                    @foreach($clubHistory as $ch)
                        <a href="{{ route('football.team', $ch['team_id']) }}?season_id={{ $ch['season_id'] }}"
                           class="group flex items-center gap-3.5 bg-slate-950 p-3.5 rounded-2xl border {{ !empty($ch['is_current']) ? 'border-emerald-500/40' : 'border-slate-800' }} hover:border-emerald-500/60 transition-all">
                            @if(!empty($ch['team']['image_path']))
                                <img src="{{ $ch['team']['image_path'] }}" alt="" class="w-11 h-11 object-contain filter drop-shadow shrink-0">
                            @else
                                <div class="w-11 h-11 rounded-xl bg-slate-800 flex items-center justify-center text-sm shrink-0">🛡️</div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-bold text-sm text-white group-hover:text-emerald-400 transition-colors truncate">{{ $ch['team']['name'] ?? ('Klub #'.$ch['team_id']) }}</h4>
                                    @if(!empty($ch['is_current']))
                                        <span class="text-[9px] font-black uppercase tracking-wider bg-emerald-500/20 text-emerald-300 px-1.5 py-0.5 rounded border border-emerald-500/30">Aktif</span>
                                    @endif
                                    @if(!empty($ch['captain']))
                                        <span title="Kapten" class="flex h-4 w-4 items-center justify-center rounded-full bg-amber-400 text-slate-950 text-[9px] font-black">C</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 text-[11px] text-slate-500 mt-0.5">
                                    <span class="font-mono">Musim {{ $ch['season_name'] ?: '#'.$ch['season_id'] }}</span>
                                    @if(!empty($ch['jersey_number']))
                                        <span class="text-slate-700">•</span>
                                        <span class="font-mono font-bold text-slate-400">No. {{ $ch['jersey_number'] }}</span>
                                    @endif
                                </div>
                            </div>
                            <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 text-slate-600 group-hover:text-emerald-400 transition-colors shrink-0"><path d="M9 6l6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </a>
                    @endforeach
                </div>
                <p class="text-[11px] text-slate-500">Tiap baris = pendaftaran skuad di satu musim. Pemain bisa terdaftar di beberapa klub karena pindah antar-musim.</p>
            @else
                <p class="text-slate-400 text-xs py-4">Belum ada riwayat klub tercatat.</p>
            @endif
        </div>

        {{-- TOPSCORER / METRIC RECORDS --}}
        <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400">
                    <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5"><path d="M8 21h8M12 17v4M7 4h10v4a5 5 0 01-10 0V4zM7 6H4v1a3 3 0 003 3M17 6h3v1a3 3 0 01-3 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <div>
                    <span class="kicker block text-[10px] font-bold uppercase text-amber-400">Peringkat</span>
                    <h3 class="text-base font-black text-white">Catatan Top Skor / Statistik</h3>
                </div>
            </div>

            @php
                // Group records per season (season heading, then metric rows).
                $tsBySeason = collect($topscorers)->groupBy(fn ($ts) => $ts['season_name'] ?: ('#' . ($ts['season_id'] ?? '-')));
            @endphp
            @if($tsBySeason->count() > 0)
                <div class="space-y-4">
                    @foreach($tsBySeason as $seasonName => $rows)
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-black text-white font-mono">Musim {{ $seasonName }}</span>
                                <span class="h-px flex-1 bg-slate-800"></span>
                                @if(!empty($rows[0]['team']))
                                    <span class="flex items-center gap-1.5 text-[11px] text-slate-500">
                                        @if(!empty($rows[0]['team']['image_path']))
                                            <img src="{{ $rows[0]['team']['image_path'] }}" alt="" class="w-3.5 h-3.5 object-contain">
                                        @endif
                                        {{ $rows[0]['team']['name'] }}
                                    </span>
                                @endif
                            </div>
                            @foreach($rows as $ts)
                                @php
                                    $cat = strtolower($ts['type_name'] ?? '');
                                    $catIcon = str_contains($cat, 'assist') ? '👟' : (str_contains($cat, 'yellow') || str_contains($cat, 'kuning') ? '🟨' : (str_contains($cat, 'red') || str_contains($cat, 'merah') ? '🟥' : '⚽'));
                                @endphp
                                <div class="bg-slate-950 px-3.5 py-2.5 rounded-xl border border-slate-800 flex items-center justify-between gap-3">
                                    <span class="text-xs font-bold text-slate-300">{{ $catIcon }} {{ $ts['type_name'] ?: 'Gol' }}</span>
                                    <div class="flex items-center gap-3 shrink-0">
                                        <span class="text-[11px] font-semibold text-slate-400">Peringkat <strong class="text-white font-mono">#{{ $ts['position'] }}</strong></span>
                                        <span class="px-3 py-1 rounded-lg bg-emerald-500/20 text-emerald-300 font-mono font-black text-xs border border-emerald-500/30">
                                            {{ $ts['total'] }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-slate-400 text-xs py-4">Belum ada catatan top skor yang tercatat di database.</p>
            @endif
        </div>

        {{-- SEASON STATISTICS (from player_statistics table) --}}
        @if(!empty($statistics))
            <div class="lg:col-span-2 bg-slate-900/90 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400">
                        <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5"><path d="M5 19V9m4.5 10V5m4.5 14v-7m4.5 7V8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    </span>
                    <div>
                        <span class="kicker block text-[10px] font-bold uppercase text-emerald-400">Statistik</span>
                        <h3 class="text-base font-black text-white">Performa Per Musim</h3>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-sm whitespace-nowrap">
                        <thead class="bg-slate-950/80 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-800 text-[11px]">
                            <tr>
                                <th class="py-3 px-3">Musim</th>
                                <th class="py-3 px-3">Klub</th>
                                <th class="py-3 px-2 text-center" title="Tampil (Appearances)">Main</th>
                                <th class="py-3 px-2 text-center" title="Menit">Menit</th>
                                <th class="py-3 px-2 text-center text-emerald-400" title="Gol">⚽</th>
                                <th class="py-3 px-2 text-center text-teal-400" title="Assist">👟</th>
                                <th class="py-3 px-2 text-center" title="Kartu Kuning">🟨</th>
                                <th class="py-3 px-2 text-center" title="Kartu Merah">🟥</th>
                                <th class="py-3 px-2 text-center text-amber-400" title="Rating">Rating</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 font-medium text-slate-200">
                            @foreach($statistics as $s)
                                <tr class="hover:bg-slate-800/50 transition-colors">
                                    <td class="py-3 px-3 font-bold text-white">{{ $s['season_name'] ?? ('#'.($s['season_id'] ?? '-')) }}</td>
                                    <td class="py-3 px-3">
                                        @if(!empty($s['team']))
                                            <span class="flex items-center gap-2">
                                                @if(!empty($s['team']['image_path']))
                                                    <img src="{{ $s['team']['image_path'] }}" alt="" class="w-4 h-4 object-contain">
                                                @endif
                                                <span class="truncate max-w-[140px]">{{ $s['team']['name'] }}</span>
                                            </span>
                                        @else
                                            <span class="text-slate-500">—</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-2 text-center font-mono">{{ $s['appearances'] ?? 0 }}</td>
                                    <td class="py-3 px-2 text-center font-mono text-slate-400">{{ $s['minutes'] ?? 0 }}'</td>
                                    <td class="py-3 px-2 text-center font-mono font-black text-emerald-400">{{ $s['goals'] ?? 0 }}</td>
                                    <td class="py-3 px-2 text-center font-mono font-bold text-teal-400">{{ $s['assists'] ?? 0 }}</td>
                                    <td class="py-3 px-2 text-center font-mono text-yellow-400">{{ $s['yellow_cards'] ?? 0 }}</td>
                                    <td class="py-3 px-2 text-center font-mono text-red-400">{{ $s['red_cards'] ?? 0 }}</td>
                                    <td class="py-3 px-2 text-center">
                                        @if(!empty($s['rating']))
                                            @php $rt = (float) $s['rating']; $rtCls = $rt >= 7 ? 'text-emerald-400' : ($rt >= 6 ? 'text-amber-400' : 'text-red-400'); @endphp
                                            <span class="font-mono font-black {{ $rtCls }}">{{ number_format($rt, 2) }}</span>
                                        @else
                                            <span class="text-slate-600">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- TRANSFER HISTORY TIMELINE --}}
        @if(count($transfers) > 0)
            <div class="lg:col-span-2 bg-slate-900/90 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
                <h3 class="text-base font-black text-white flex items-center gap-2">
                    💸 Riwayat Transfer Pemain
                </h3>

                <div class="space-y-3">
                    @foreach($transfers as $tr)
                        @php
                            // Compact transfer fee (euros): 45000000 -> €45.0M, 850000 -> €850K
                            $amount = $tr['amount'] ?? null;
                            $feeText = null;
                            if (!empty($amount) && $amount > 0) {
                                if ($amount >= 1000000) {
                                    $feeText = '€' . rtrim(rtrim(number_format($amount / 1000000, 1), '0'), '.') . 'M';
                                } elseif ($amount >= 1000) {
                                    $feeText = '€' . round($amount / 1000) . 'K';
                                } else {
                                    $feeText = '€' . number_format($amount);
                                }
                            }
                            $typeLabel = $tr['type_name'] ?? ($tr['type'] ?? 'Transfer');
                        @endphp
                        <div class="bg-slate-950 p-4 rounded-2xl border border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-emerald-400">{{ $typeLabel }}</span>
                                    @if($feeText)
                                        <span class="text-[11px] font-mono font-black px-2 py-0.5 rounded-md bg-amber-500/15 text-amber-300 border border-amber-500/30">{{ $feeText }}</span>
                                    @endif
                                </div>
                                <span class="text-xs text-slate-400 font-medium">{{ $tr['date'] ? date('d F Y', strtotime($tr['date'])) : 'Resmi' }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-xs font-bold">
                                <span class="flex items-center gap-1.5 bg-slate-900 px-2.5 py-1.5 rounded-xl text-slate-300 border border-slate-800">
                                    @if(!empty($tr['from_team']['image_path']))
                                        <img src="{{ $tr['from_team']['image_path'] }}" alt="" class="w-4 h-4 object-contain">
                                    @endif
                                    <span class="truncate max-w-[120px]">{{ $tr['from_team']['name'] ?? 'Klub Asal' }}</span>
                                </span>
                                <span class="text-emerald-400 font-mono font-black">&rarr;</span>
                                <span class="flex items-center gap-1.5 bg-emerald-950/60 px-2.5 py-1.5 rounded-xl text-emerald-300 border border-emerald-800/50">
                                    @if(!empty($tr['to_team']['image_path']))
                                        <img src="{{ $tr['to_team']['image_path'] }}" alt="" class="w-4 h-4 object-contain">
                                    @endif
                                    <span class="truncate max-w-[120px]">{{ $tr['to_team']['name'] ?? 'Klub Tujuan' }}</span>
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
