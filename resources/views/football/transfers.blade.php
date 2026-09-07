@extends('layouts.app')

@section('title', __('football.transfers.title'))

@section('content')
    <div class="space-y-8">
        {{-- Header --}}
        <div class="relative overflow-hidden rounded-xl border border-line bg-surface p-6 sm:p-8">
            <div class="relative z-10">
                <span class="kicker mb-3 block text-xs font-bold uppercase text-primary">{{ __('football.transfers.kicker') }}</span>
                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">{{ __('football.transfers.heading') }}</h1>
                <p class="mt-1.5 text-xs sm:text-sm text-body">{{ __('football.transfers.subheading') }}</p>
            </div>
        </div>

        @if(!empty($transfers))
            <div class="space-y-3">
                @foreach($transfers as $tr)
                    @include('football.partials.transfer-row', ['tr' => $tr])
                @endforeach
            </div>
        @else
            <div class="rounded-xl border border-dashed border-line bg-surface p-12 text-center">
                <p class="text-base font-bold text-white">{{ __('football.transfers.empty') }}</p>
                <p class="mt-1 text-xs text-white0">{{ __('football.transfers.empty_hint') }}</p>
            </div>
        @endif
    </div>
@endsection
