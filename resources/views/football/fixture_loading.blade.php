@extends('layouts.app')

@section('title', 'Menyiapkan Pertandingan...')

@section('content')
    <div class="mx-auto max-w-lg py-16 text-center"
         x-data="fixtureLoader({{ $fixtureId }})" x-init="start()">

        {{-- Spinner --}}
        <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center">
            <svg viewBox="0 0 24 24" fill="none" class="h-16 w-16 animate-spin text-emerald-500">
                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" opacity="0.2"/>
                <path d="M21 12a9 9 0 00-9-9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </div>

        <template x-if="!failed">
            <div>
                <span class="kicker block text-[10px] font-bold uppercase text-emerald-400 mb-2">Menyiapkan Data</span>
                <h1 class="text-2xl font-black text-white">Mengambil data pertandingan…</h1>
                <p class="mt-2 text-sm text-slate-400" x-text="statusText">Menghubungi server &amp; mengunduh detail laga dari Sportmonks.</p>
            </div>
        </template>

        <template x-if="failed">
            <div>
                <h1 class="text-2xl font-black text-white">Pertandingan tidak ditemukan</h1>
                <p class="mt-2 text-sm text-slate-400">Data laga ini belum tersedia dan gagal diambil dari sumber.</p>
                <div class="mt-6 flex items-center justify-center gap-3">
                    <button @click="failed=false; start()" class="rounded-xl bg-emerald-500 hover:bg-emerald-400 px-4 py-2.5 text-sm font-bold text-slate-950 transition-colors">Coba lagi</button>
                    <a href="{{ route('football.index') }}" class="rounded-xl border border-slate-800 bg-slate-900 px-4 py-2.5 text-sm font-bold text-slate-300 hover:text-white transition-colors">Kembali ke Portal</a>
                </div>
            </div>
        </template>
    </div>

    <script>
        function fixtureLoader(fixtureId) {
            return {
                failed: false,
                statusText: 'Menghubungi server & mengunduh detail laga dari Sportmonks.',
                async start() {
                    this.failed = false;
                    this.statusText = 'Mengunduh detail laga… ini bisa memakan beberapa detik.';
                    try {
                        const res = await fetch(`{{ url('/football/fixtures') }}/${fixtureId}/prepare`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                        });
                        const data = await res.json();
                        if (data.ready) {
                            this.statusText = 'Selesai! Mengalihkan…';
                            window.location = `{{ url('/football/fixtures') }}/${fixtureId}`;
                            return;
                        }
                    } catch (e) { /* fallthrough to failed */ }
                    this.failed = true;
                },
            }
        }
    </script>
@endsection
