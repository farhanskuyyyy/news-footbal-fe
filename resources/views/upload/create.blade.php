@extends('layouts.app')

@section('title', __('upload.title'))

@section('content')
    <div class="space-y-8">
        {{-- Header --}}
        <div class="relative overflow-hidden border border-line rounded-xl p-6 sm:p-8 relative overflow-hidden">
            <div class="relative z-10">
                <span class="kicker block text-xs font-bold uppercase text-primary mb-3">{{ __('upload.kicker') }}</span>
                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">{{ __('upload.heading') }}</h1>
                <p class="text-xs sm:text-sm text-body mt-1.5 max-w-2xl">
                    {!! __('upload.subheading', ['queue' => '<code class="rounded-lg bg-ink px-1.5 py-0.5 text-accent font-mono border border-line">image_upload_queue</code>']) !!}
                </p>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-line p-4 text-sm text-accent">
                <p class="font-semibold">{{ session('status') }}</p>
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-line p-4 text-sm text-accent">
                <p class="font-semibold">{{ __('upload.validation_failed') }}</p>
                <ul class="mt-2 list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid gap-6 md:grid-cols-2">
            {{-- Form Upload --}}
            <div class="rounded-xl border border-line bg-surface p-6">
                <h2 class="mb-4 text-lg font-bold text-white">{{ __('upload.form_heading') }}</h2>
                <form action="{{ route('upload.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label for="image" class="mb-2 block text-sm font-semibold text-white">{{ __('upload.choose_image') }}</label>
                        <div class="relative flex min-h-[160px] flex-col items-center justify-center rounded-xl border-2 border-dashed border-line bg-ink p-6 text-center hover:border-line transition">
                            <svg class="mb-3 h-10 w-10 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span class="text-sm font-semibold text-white">{{ __('upload.dropzone') }}</span>
                            <span class="mt-1 text-xs text-muted">{{ __('upload.dropzone_hint') }}</span>
                            <input id="image" name="image" type="file" accept="image/*" class="absolute inset-0 cursor-pointer opacity-0" onchange="previewImage(event)" required>
                        </div>
                    </div>

                    {{-- Preview Box --}}
                    <div id="preview-container" class="mb-4 hidden rounded-xl border border-line bg-ink p-3">
                        <p class="mb-2 text-xs font-semibold text-muted">{{ __('upload.preview') }}</p>
                        <img id="image-preview" src="#" alt="Preview" class="max-h-48 rounded-xl object-cover">
                    </div>

                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-primary hover:bg-accent px-4 py-2.5 text-sm font-bold text-white transition-all">
                        {{ __('upload.submit') }}
                    </button>
                </form>
            </div>

            {{-- Info & Status --}}
            <div class="flex flex-col gap-6">
                @if (session('uploaded_image'))
                    @php $img = session('uploaded_image'); @endphp
                    <div class="rounded-xl border border-line bg-surface p-6">
                        <h2 class="mb-3 text-lg font-bold text-white">{{ __('upload.result_heading') }}</h2>
                        <div class="mb-4 overflow-hidden rounded-xl border border-line bg-ink">
                            <img src="{{ $img['url'] }}" alt="{{ $img['filename'] }}" class="max-h-56 w-full object-contain">
                        </div>
                        <dl class="space-y-2 text-sm text-white">
                            <div class="flex justify-between">
                                <dt class="font-medium text-muted">{{ __('upload.original_name') }}</dt>
                                <dd class="font-semibold">{{ $img['original_name'] }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="font-medium text-muted">{{ __('upload.stored_name') }}</dt>
                                <dd class="font-mono text-xs">{{ $img['filename'] }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="font-medium text-muted">{{ __('upload.size') }}</dt>
                                <dd class="font-mono">{{ $img['size_formatted'] }}</dd>
                            </div>
                            <div class="flex justify-between items-center pt-2 border-t border-line">
                                <dt class="font-medium text-muted">{{ __('upload.mq_status') }}</dt>
                                <dd class="rounded-lg px-2.5 py-0.5 text-xs font-semibold {{ !empty($img['mq_ok']) ? 'text-accent border border-line' : 'text-gold border border-line' }}">
                                    {{ !empty($img['mq_ok']) ? __('upload.mq_sent') : __('upload.mq_failed') }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                @endif

                <div class="rounded-xl border border-line bg-surface p-6">
                    <h2 class="mb-3 text-lg font-bold text-white">{{ __('upload.config_heading') }}</h2>
                    <ul class="space-y-3 text-sm text-body">
                        <li class="flex items-center justify-between gap-3">
                            <span>{{ __('upload.rabbitmq_host') }}</span>
                            <code class="rounded-lg bg-ink px-2 py-1 font-mono text-xs text-white border border-line">{{ config('rabbitmq.host') }}:{{ config('rabbitmq.port') }}</code>
                        </li>
                        <li class="flex items-center justify-between gap-3">
                            <span>{{ __('upload.rabbitmq_queue') }}</span>
                            <code class="rounded-lg bg-ink px-2 py-1 font-mono text-xs text-accent border border-line">{{ config('rabbitmq.queue') }}</code>
                        </li>
                        <li class="flex items-center justify-between gap-3">
                            <span>{{ __('upload.grafana') }}</span>
                            <code class="rounded-lg bg-ink px-2 py-1 font-mono text-xs text-white border border-line">http://localhost:3001</code>
                        </li>
                        <li class="flex items-center justify-between gap-3">
                            <span>{{ __('upload.rabbitmq_manager') }}</span>
                            <code class="rounded-lg bg-ink px-2 py-1 font-mono text-xs text-white border border-line">http://localhost:15672</code>
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
