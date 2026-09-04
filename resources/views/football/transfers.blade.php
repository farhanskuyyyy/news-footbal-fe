@extends('layouts.app')

@section('title', 'Bursa Transfer - KREASIBALL')

@section('content')
    <div class="space-y-8">
        {{-- Header --}}
        <div class="pitch-stripes relative overflow-hidden rounded-3xl border border-slate-800 bg-gradient-to-r from-slate-900 via-slate-900/90 to-slate-950 p-6 sm:p-8 shadow-2xl">
            <div class="absolute -right-16 -top-16 h-72 w-72 rounded-full bg-emerald-500/10 blur-3xl pointer-events-none"></div>
            <div class="relative z-10">
                <span class="kicker mb-3 block text-[10px] font-bold uppercase text-emerald-400">Bursa Transfer</span>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">Transfer Terkini</h1>
                <p class="mt-1.5 text-xs sm:text-sm text-slate-400">Kepindahan pemain terbaru lintas liga — langsung dari sumber.</p>
            </div>
        </div>

        @if(!empty($transfers))
            <div class="space-y-3">
                @foreach($transfers as $tr)
                    @php
                        $pl = $tr['player'] ?? [];
                        $from = $tr['fromteam'] ?? $tr['from_team'] ?? [];
                        $to = $tr['toteam'] ?? $tr['to_team'] ?? [];
                        $typeName = $tr['type']['name'] ?? null;
                        $amount = $tr['amount'] ?? null;
                        $fee = null;
                        if (! empty($amount) && $amount > 0) {
                            if ($amount >= 1000000) {
                                $fee = '€'.rtrim(rtrim(number_format($amount / 1000000, 1), '0'), '.').'M';
                            } elseif ($amount >= 1000) {
                                $fee = '€'.round($amount / 1000).'K';
                            } else {
                                $fee = '€'.number_format($amount);
                            }
                        }
                        $tLower = strtolower($typeName ?? '');
                        $isLoan = str_contains($tLower, 'loan') || str_contains($tLower, 'pinjam');
                    @endphp
                    <div class="flex flex-col gap-4 rounded-2xl border border-slate-800 bg-slate-900/80 p-4 shadow-lg transition-all hover:border-slate-700 sm:flex-row sm:items-center sm:justify-between">
                        {{-- Player --}}
                        <div class="flex items-center gap-3 min-w-0 sm:w-1/3">
                            <a href="{{ route('football.player', $pl['id'] ?? 0) }}" class="shrink-0">
                                @if(!empty($pl['image_path']))
                                    <img src="{{ $pl['image_path'] }}" alt="" class="h-11 w-11 rounded-full object-cover border border-slate-700">
                                @else
                                    <div class="flex h-11 w-11 items-center justify-center rounded-full bg-slate-800 text-lg">👤</div>
                                @endif
                            </a>
                            <div class="min-w-0">
                                <a href="{{ route('football.player', $pl['id'] ?? 0) }}" class="block truncate text-sm font-black text-white hover:text-emerald-400 transition-colors">{{ $pl['display_name'] ?? $pl['name'] ?? 'Pemain' }}</a>
                                <span class="font-mono text-[11px] text-slate-500">{{ $tr['date'] ? \Illuminate\Support\Carbon::parse($tr['date'])->locale('id')->translatedFormat('d M Y') : 'Resmi' }}</span>
                            </div>
                        </div>

                        {{-- From → To --}}
                        <div class="flex flex-1 items-center justify-center gap-2.5 text-xs font-bold">
                            <a href="{{ !empty($from['id']) ? route('football.team', $from['id']) : '#' }}" class="flex items-center gap-1.5 rounded-lg bg-slate-950 px-2.5 py-1.5 text-slate-300 border border-slate-800 hover:border-slate-700 transition-colors max-w-[42%] min-w-0">
                                @if(!empty($from['image_path']))<img src="{{ $from['image_path'] }}" alt="" class="h-4 w-4 object-contain shrink-0">@endif
                                <span class="truncate">{{ $from['name'] ?? 'Klub Asal' }}</span>
                            </a>
                            <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 shrink-0 text-emerald-400"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <a href="{{ !empty($to['id']) ? route('football.team', $to['id']) : '#' }}" class="flex items-center gap-1.5 rounded-lg bg-emerald-950/60 px-2.5 py-1.5 text-emerald-300 border border-emerald-800/50 hover:border-emerald-700 transition-colors max-w-[42%] min-w-0">
                                @if(!empty($to['image_path']))<img src="{{ $to['image_path'] }}" alt="" class="h-4 w-4 object-contain shrink-0">@endif
                                <span class="truncate">{{ $to['name'] ?? 'Klub Tujuan' }}</span>
                            </a>
                        </div>

                        {{-- Type + fee --}}
                        <div class="flex items-center justify-end gap-2 sm:w-1/5">
                            @if($typeName)
                                <span class="rounded-md px-2 py-0.5 text-[10px] font-black uppercase tracking-wider {{ $isLoan ? 'bg-blue-500/15 text-blue-300 border border-blue-500/25' : 'bg-slate-800 text-slate-300 border border-slate-700' }}">{{ $typeName }}</span>
                            @endif
                            @if($fee)
                                <span class="rounded-md bg-amber-500/15 px-2 py-0.5 font-mono text-xs font-black text-amber-300 border border-amber-500/30">{{ $fee }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="rounded-3xl border border-dashed border-slate-800 bg-slate-900/40 p-12 text-center">
                <p class="text-base font-bold text-slate-200">Belum ada data transfer.</p>
                <p class="mt-1 text-xs text-slate-500">Butuh koneksi ke sumber (Sportmonks) aktif.</p>
            </div>
        @endif
    </div>
@endsection
