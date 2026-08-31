@extends('layouts.app')

@section('title', 'Login Admin - KREASIBALL')

@section('content')
    <div class="mx-auto max-w-sm py-12">
        <div class="rounded-3xl border border-slate-800 bg-slate-900/80 p-8 shadow-2xl">
            <div class="mb-6 text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-400 text-slate-950 shadow-lg shadow-emerald-500/25">
                    <svg viewBox="0 0 24 24" fill="none" class="h-7 w-7"><rect x="5" y="11" width="14" height="9" rx="2" stroke="currentColor" stroke-width="1.8"/><path d="M8 11V8a4 4 0 018 0v3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                </div>
                <span class="kicker block text-[10px] font-bold uppercase text-emerald-400 mb-1">Panel Admin</span>
                <h1 class="text-xl font-black text-white">Masuk ke Dashboard</h1>
            </div>

            @if ($errors->any())
                <p class="mb-4 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm font-medium text-red-300">
                    {{ $errors->first() }}
                </p>
            @endif

            <form method="POST" action="{{ route('login.attempt') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-400">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                           class="w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm font-medium text-white placeholder:text-slate-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div>
                    <label for="password" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-400">Password</label>
                    <input id="password" name="password" type="password" required
                           class="w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm font-medium text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <label class="flex items-center gap-2 text-xs text-slate-400">
                    <input type="checkbox" name="remember" class="rounded border-slate-700 bg-slate-950 text-emerald-500 focus:ring-emerald-500">
                    Ingat saya
                </label>
                <button type="submit"
                        class="w-full rounded-xl bg-emerald-500 hover:bg-emerald-400 px-4 py-2.5 text-sm font-bold text-slate-950 shadow-lg shadow-emerald-500/20 transition-all">
                    Masuk
                </button>
            </form>
        </div>
    </div>
@endsection
