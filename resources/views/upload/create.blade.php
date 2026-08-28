@extends('layouts.app')

@section('title', 'Upload Gambar & Queue Event')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight">📤 Upload Gambar & Push Event RabbitMQ</h1>
        <p class="mt-1 text-sm text-gray-600">
            Upload file gambar ke Laravel storage local, lalu sistem akan secara otomatis mempublikasikan event metadata ke queue RabbitMQ (<code class="rounded bg-gray-100 px-1 py-0.5 text-blue-600 font-mono">image_upload_queue</code>) untuk dikonsumsi oleh Go Consumer.
        </p>
    </div>

    @if (session('status'))
        <div class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800">
            <p class="font-semibold">{{ session('status') }}</p>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            <p class="font-semibold">Terjadi kesalahan validasi:</p>
            <ul class="mt-2 list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-6 md:grid-cols-2">
        <!-- Form Upload -->
        <div class="rounded-xl border bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold text-gray-900">Form Upload</h2>
            <form action="{{ route('upload.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label for="image" class="mb-2 block text-sm font-medium text-gray-700">Pilih Gambar</label>
                    <div class="relative flex min-h-[160px] flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 p-6 text-center hover:border-blue-500 transition">
                        <svg class="mb-3 h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span class="text-sm font-medium text-gray-600">Klik untuk memilih file gambar</span>
                        <span class="mt-1 text-xs text-gray-400">PNG, JPG, JPEG, WEBP, GIF (Maks. 5MB)</span>
                        <input id="image" name="image" type="file" accept="image/*" class="absolute inset-0 cursor-pointer opacity-0" onchange="previewImage(event)" required>
                    </div>
                </div>

                <!-- Preview Box -->
                <div id="preview-container" class="mb-4 hidden rounded-lg border bg-gray-50 p-3">
                    <p class="mb-2 text-xs font-semibold text-gray-500">Preview Gambar:</p>
                    <img id="image-preview" src="#" alt="Preview" class="max-h-48 rounded object-cover">
                </div>

                <button type="submit" class="w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition">
                    🚀 Upload & Publish ke RabbitMQ
                </button>
            </form>
        </div>

        <!-- Info & Status Upload Terakhir -->
        <div class="flex flex-col gap-6">
            @if (session('uploaded_image'))
                @php $img = session('uploaded_image'); @endphp
                <div class="rounded-xl border border-blue-100 bg-blue-50/50 p-6 shadow-sm">
                    <h2 class="mb-3 text-lg font-semibold text-blue-900">Result Upload Terakhir</h2>
                    <div class="mb-4 overflow-hidden rounded-lg border bg-white">
                        <img src="{{ $img['url'] }}" alt="{{ $img['filename'] }}" class="max-h-56 w-full object-contain bg-gray-100">
                    </div>
                    <dl class="space-y-2 text-sm text-gray-700">
                        <div class="flex justify-between">
                            <dt class="font-medium text-gray-500">Nama File Asli:</dt>
                            <dd class="font-semibold">{{ $img['original_name'] }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="font-medium text-gray-500">Nama simpan:</dt>
                            <dd class="font-mono text-xs">{{ $img['filename'] }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="font-medium text-gray-500">Ukuran:</dt>
                            <dd>{{ $img['size_formatted'] }}</dd>
                        </div>
                        <div class="flex justify-between items-center pt-2 border-t">
                            <dt class="font-medium text-gray-500">Status RabbitMQ:</dt>
                            <dd class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ str_contains($img['mq_status'], 'Terkirim') ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $img['mq_status'] }}
                            </dd>
                        </div>
                    </dl>
                </div>
            @endif

            <div class="rounded-xl border bg-white p-6 shadow-sm">
                <h2 class="mb-3 text-lg font-semibold text-gray-900">⚙️ Status Konfigurasi</h2>
                <ul class="space-y-3 text-sm text-gray-600">
                    <li class="flex items-center justify-between">
                        <span>RabbitMQ Host</span>
                        <code class="rounded bg-gray-100 px-2 py-1 font-mono text-xs">{{ config('rabbitmq.host') }}:{{ config('rabbitmq.port') }}</code>
                    </li>
                    <li class="flex items-center justify-between">
                        <span>RabbitMQ Queue</span>
                        <code class="rounded bg-gray-100 px-2 py-1 font-mono text-xs text-blue-600">{{ config('rabbitmq.queue') }}</code>
                    </li>
                    <li class="flex items-center justify-between">
                        <span>Grafana Dashboard</span>
                        <code class="rounded bg-gray-100 px-2 py-1 font-mono text-xs text-purple-600">http://localhost:3001</code>
                    </li>
                    <li class="flex items-center justify-between">
                        <span>RabbitMQ Manager</span>
                        <code class="rounded bg-gray-100 px-2 py-1 font-mono text-xs text-green-600">http://localhost:15672</code>
                    </li>
                </ul>
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
