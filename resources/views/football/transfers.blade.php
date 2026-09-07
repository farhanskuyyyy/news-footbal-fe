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
                    @include('football.partials.transfer-row', ['tr' => $tr])
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
