@extends('layouts.app')

@section('title', __('auth.title'))

@section('content')
    <div class="mx-auto max-w-sm py-12">
        <div class="rounded-xl border border-line bg-surface p-8">
            <div class="mb-6 text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-xl bg-surface text-muted">
                    <svg viewBox="0 0 24 24" fill="none" class="icon h-7 w-7"><rect x="5" y="11" width="14" height="9" rx="2" stroke="currentColor" stroke-width="1.8"/><path d="M8 11V8a4 4 0 018 0v3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                </div>
                <span class="kicker block text-xs font-bold uppercase text-primary mb-1">{{ __('auth.kicker') }}</span>
                <h1 class="text-xl font-bold text-white">{{ __('auth.heading') }}</h1>
            </div>

            @if ($errors->any())
                <p class="mb-4 rounded-xl border border-line px-4 py-3 text-sm font-medium text-accent">
                    {{ $errors->first() }}
                </p>
            @endif

            <form method="POST" action="{{ route('login.attempt') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-body">{{ __('auth.email') }}</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                           class="w-full rounded-xl border border-line bg-ink px-3.5 py-2.5 text-sm font-medium text-white placeholder:text-muted focus:outline-none focus:ring-2 focus:ring-line focus:border-line">
                </div>
                <div>
                    <label for="password" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-body">{{ __('auth.password') }}</label>
                    <input id="password" name="password" type="password" required
                           class="w-full rounded-xl border border-line bg-ink px-3.5 py-2.5 text-sm font-medium text-white focus:outline-none focus:ring-2 focus:ring-line focus:border-line">
                </div>
                <label class="flex items-center gap-2 text-xs text-body">
                    <input type="checkbox" name="remember" class="rounded-lg border-line bg-ink text-accent focus:ring-line">
                    {{ __('auth.remember') }}
                </label>
                <button type="submit"
                        class="w-full rounded-xl bg-primary hover:bg-accent px-4 py-2.5 text-sm font-bold text-white transition-all">
                    {{ __('auth.submit') }}
                </button>
            </form>
        </div>
    </div>
@endsection
