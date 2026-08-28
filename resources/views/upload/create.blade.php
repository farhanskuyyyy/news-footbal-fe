@extends('layouts.app')

@section('title', 'Upload Gambar & Queue Event')

@section('content')
    <div class="space-y-8">
        {{-- Header --}}
        <div class="pitch-stripes bg-gradient-to-r from-slate-900 via-slate-900/90 to-slate-950 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl relative overflow-hidden">
            <div class="absolute -right-16 -top-16 w-72 h-72 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="relative z-10">
                <span class="kicker block text-[10px] font-bold uppercase text-emerald-400 mb-3">UPLOAD PIPELINE</span>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">Upload Gambar &amp; Push Event</h1>
                <p class="text-xs sm:text-sm text-slate-400 mt-1.5 max-w-2xl">
                    Upload gambar ke storage lokal Laravel; sistem otomatis publish metadata ke queue RabbitMQ
                    (<code class="rounded bg-slate-950 px-1.5 py-0.5 text-emerald-400 font-mono border border-slate-800">image_upload_queue</code>)
                    untuk dikonsumsi Go Consumer.
                </p>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-sm text-emerald-300">
                <p class="font-semibold">{{ session('status') }}</p>
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-red-500/30 bg-red-500/10 p-4 text-sm text-red-300">
                <p class="font-semibold">Terjadi kesalahan validasi:</p>
                <ul class="mt-2 list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid gap-6 md:grid-cols-2">
            {{-- Form Upload --}}
            <div class="rounded-3xl border border-slate-800 bg-slate-900/90 p-6 shadow-xl">
                <h2 class="mb-4 text-lg font-black text-white">Form Upload</h2>
                <form action="{{ route('upload.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label for="image" class="mb-2 block text-sm font-semibold text-slate-300">Pilih Gambar</label>
                        <div class="relative flex min-h-[160px] flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-700 bg-slate-950/80 p-6 text-center hover:border-emerald-500/60 transition">
                            <svg class="mb-3 h-10 w-10 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span class="text-sm font-semibold text-slate-300">Klik untuk memilih file gambar</span>
                            <span class="mt-1 text-xs text-slate-500">PNG, JPG, JPEG, WEBP, GIF (Maks. 5MB)</span>
                            <input id="image" name="image" type="file" accept="image/*" class="absolute inset-0 cursor-pointer opacity-0" onchange="previewImage(event)" required>
                        </div>
                    </div>

                    {{-- Preview Box --}}
                    <div id="preview-container" class="mb-4 hidden rounded-2xl border border-slate-800 bg-slate-950 p-3">
                        <p class="mb-2 text-xs font-semibold text-slate-500">Preview Gambar:</p>
                        <img id="image-preview" src="#" alt="Preview" class="max-h-48 rounded-xl object-cover">
                    </div>

                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 px-4 py-2.5 text-sm font-bold text-slate-950 shadow-lg shadow-emerald-500/20 transition-all">
                        🚀 Upload &amp; Publish ke RabbitMQ
                    </button>
                </form>
            </div>

            {{-- Info & Status --}}
            <div class="flex flex-col gap-6">
                @if (session('uploaded_image'))
                    @php $img = session('uploaded_image'); @endphp
                    <div class="rounded-3xl border border-emerald-500/20 bg-slate-900/90 p-6 shadow-xl">
                        <h2 class="mb-3 text-lg font-black text-white">Result Upload Terakhir</h2>
                        <div class="mb-4 overflow-hidden rounded-2xl border border-slate-800 bg-slate-950">
                            <img src="{{ $img['url'] }}" alt="{{ $img['filename'] }}" class="max-h-56 w-full object-contain">
                        </div>
                        <dl class="space-y-2 text-sm text-slate-300">
                            <div class="flex justify-between">
                                <dt class="font-medium text-slate-500">Nama File Asli:</dt>
                                <dd class="font-semibold">{{ $img['original_name'] }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="font-medium text-slate-500">Nama simpan:</dt>
                                <dd class="font-mono text-xs">{{ $img['filename'] }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="font-medium text-slate-500">Ukuran:</dt>
                                <dd class="font-mono">{{ $img['size_formatted'] }}</dd>
                            </div>
                            <div class="flex justify-between items-center pt-2 border-t border-slate-800">
                                <dt class="font-medium text-slate-500">Status RabbitMQ:</dt>
                                <dd class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ str_contains($img['mq_status'], 'Terkirim') ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-amber-500/20 text-amber-300 border border-amber-500/30' }}">
                                    {{ $img['mq_status'] }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                @endif

                <div class="rounded-3xl border border-slate-800 bg-slate-900/90 p-6 shadow-xl">
                    <h2 class="mb-3 text-lg font-black text-white">⚙️ Status Konfigurasi</h2>
                    <ul class="space-y-3 text-sm text-slate-400">
                        <li class="flex items-center justify-between gap-3">
                            <span>RabbitMQ Host</span>
                            <code class="rounded-lg bg-slate-950 px-2 py-1 font-mono text-xs text-slate-300 border border-slate-800">{{ config('rabbitmq.host') }}:{{ config('rabbitmq.port') }}</code>
                        </li>
                        <li class="flex items-center justify-between gap-3">
                            <span>RabbitMQ Queue</span>
                            <code class="rounded-lg bg-slate-950 px-2 py-1 font-mono text-xs text-emerald-400 border border-slate-800">{{ config('rabbitmq.queue') }}</code>
                        </li>
                        <li class="flex items-center justify-between gap-3">
                            <span>Grafana Dashboard</span>
                            <code class="rounded-lg bg-slate-950 px-2 py-1 font-mono text-xs text-slate-300 border border-slate-800">http://localhost:3001</code>
                        </li>
                        <li class="flex items-center justify-between gap-3">
                            <span>RabbitMQ Manager</span>
                            <code class="rounded-lg bg-slate-950 px-2 py-1 font-mono text-xs text-slate-300 border border-slate-800">http://localhost:15672</code>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        function previewImage(event) {
            const input = event.target;
            const container = document.getElementById('preview-container');
            const preview = document.getElementById('image-preview');

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    container.classList.remove('hidden');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
@endsection
