@extends('layouts.app')

@section('title', 'KREASIBALL — Portal Sepak Bola')

@section('content')
    <div class="space-y-10">

        {{-- HERO --}}
        <div class="pitch-stripes relative overflow-hidden rounded-3xl border border-slate-800 bg-gradient-to-br from-slate-900 via-slate-900 to-slate-950 p-8 sm:p-12 shadow-2xl">
            <div class="absolute -right-20 -top-20 h-80 w-80 rounded-full bg-emerald-500/10 blur-3xl pointer-events-none"></div>
            <div class="relative z-10 max-w-2xl">
                <span class="kicker mb-3 inline-flex items-center gap-2 text-[10px] font-bold uppercase text-emerald-400">
                    <span class="relative flex h-2 w-2"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span><span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span></span>
                    Portal Real-time
                </span>
                <h1 class="text-3xl sm:text-5xl font-black tracking-tight text-white">Semua Sepak Bola, <span class="text-emerald-400">Satu Tempat.</span></h1>
                <p class="mt-3 text-sm sm:text-base text-slate-400">Skor langsung, jadwal, klasemen, statistik pemain, transfer, dan berita — diperbarui dari sumber.</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('football.live') }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 px-4 py-2.5 text-sm font-bold text-slate-950 shadow-lg shadow-emerald-500/20 transition-all">Skor Langsung</a>
                    <a href="{{ route('football.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-700 bg-slate-900 px-4 py-2.5 text-sm font-bold text-slate-200 hover:border-slate-600 transition-colors">Portal Bola</a>
                </div>
            </div>
        </div>

        {{-- LIVE STRIP --}}
        @if(!empty($live))
            <section class="space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="flex items-center gap-2 text-lg font-black text-white">
                        <span class="relative flex h-2.5 w-2.5"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-500 opacity-75"></span><span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-red-500"></span></span>
                        Sedang Berlangsung
                    </h2>
                    <a href="{{ route('football.live') }}" class="text-xs font-bold text-emerald-400 hover:underline">Semua →</a>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    @foreach($live as $f)
                        @include('football.partials.live-card', ['f' => $f])
                    @endforeach
                </div>
            </section>
        @endif

        {{-- MAIN GRID: today + featured --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- Today's matches --}}
            <section class="lg:col-span-2 space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-black text-white">Pertandingan Hari Ini</h2>
                    <a href="{{ route('football.matchday') }}" class="text-xs font-bold text-emerald-400 hover:underline">Kalender →</a>
                </div>
                @if(!empty($today))
                    @php $byLeague = collect($today)->groupBy(fn ($f) => $f['league']['name'] ?? 'Lainnya'); @endphp
                    <div class="space-y-5">
                        @foreach($byLeague->take(4) as $leagueName => $rows)
                            <div class="space-y-2.5">
                                <h3 class="flex items-center gap-2 text-xs font-black uppercase tracking-wider text-slate-500">
                                    @if(!empty($rows[0]['league']['image_path']))<img src="{{ $rows[0]['league']['image_path'] }}" alt="" class="h-4 w-4 object-contain">@endif
                                    {{ $leagueName }}
                                </h3>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    @foreach($rows->take(4) as $f)
                                        @include('football.partials.live-card', ['f' => $f])
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-2xl border border-dashed border-slate-800 bg-slate-900/40 p-8 text-center text-sm text-slate-500">Tidak ada pertandingan hari ini. Cek <a href="{{ route('football.matchday') }}" class="text-emerald-400 hover:underline">kalender</a>.</div>
                @endif
            </section>

            {{-- Featured league: mini standings + topscorers --}}
            <aside class="space-y-6">
                @if($featured)
                    {{-- Mini standings --}}
                    <div class="rounded-3xl border border-slate-800 bg-slate-900/80 p-5 shadow-xl">
                        <div class="mb-4 flex items-center gap-2">
                            @if(!empty($featured['league']['image_path']))<img src="{{ $featured['league']['image_path'] }}" alt="" class="h-6 w-6 object-contain">@endif
                            <div class="min-w-0">
                                <span class="kicker block text-[9px] font-bold uppercase text-emerald-400">Klasemen</span>
                                <h3 class="truncate text-sm font-black text-white">{{ $featured['league']['name'] }}</h3>
                            </div>
                        </div>
                        <div class="space-y-1">
                            @foreach($featured['standings'] as $st)
                                <a href="{{ route('football.index', ['league_id' => $featured['league']['id'], 'season_id' => $featured['season']['id']]) }}"
                                   class="flex items-center gap-3 rounded-xl px-2.5 py-2 hover:bg-slate-800/50 transition-colors">
                                    <span class="w-5 text-center font-mono text-xs font-bold text-slate-500">{{ $st['position'] ?? $loop->iteration }}</span>
                                    @if(!empty($st['team']['image_path']))<img src="{{ $st['team']['image_path'] }}" alt="" class="h-5 w-5 object-contain">@else<span class="h-5 w-5"></span>@endif
                                    <span class="flex-1 truncate text-xs font-bold text-slate-200">{{ $st['team']['name'] ?? '-' }}</span>
                                    <span class="font-mono text-[11px] text-slate-500">{{ $st['played'] ?? 0 }}</span>
                                    <span class="w-6 text-right font-mono text-xs font-black text-emerald-400">{{ $st['points'] ?? 0 }}</span>
                                </a>
                            @endforeach
                        </div>
                        <a href="{{ route('football.index', ['league_id' => $featured['league']['id'], 'season_id' => $featured['season']['id']]) }}" class="mt-3 block text-center text-xs font-bold text-emerald-400 hover:underline">Klasemen lengkap →</a>
                    </div>

                    {{-- Top scorers --}}
                    @if(!empty($featured['topscorers']))
                        <div class="rounded-3xl border border-slate-800 bg-slate-900/80 p-5 shadow-xl">
                            <span class="kicker mb-3 block text-[9px] font-bold uppercase text-amber-400">Top Skor</span>
                            <div class="space-y-2">
                                @foreach($featured['topscorers'] as $ts)
                                    <a href="{{ route('football.player', $ts['player']['id'] ?? ($ts['player_id'] ?? 0)) }}" class="flex items-center gap-3 rounded-xl px-2 py-1.5 hover:bg-slate-800/50 transition-colors">
                                        <span class="w-4 text-center font-mono text-xs font-bold text-slate-500">{{ $loop->iteration }}</span>
                                        @if(!empty($ts['player']['image_path']))<img src="{{ $ts['player']['image_path'] }}" alt="" class="h-7 w-7 rounded-full object-cover border border-slate-700">@else<div class="flex h-7 w-7 items-center justify-center rounded-full bg-slate-800 text-xs">👤</div>@endif
                                        <span class="flex-1 truncate text-xs font-bold text-slate-200">{{ $ts['player']['display_name'] ?? $ts['player']['name'] ?? 'Pemain' }}</span>
                                        <span class="rounded-md bg-emerald-500/15 px-2 py-0.5 font-mono text-xs font-black text-emerald-300 border border-emerald-500/20">{{ $ts['total'] ?? 0 }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif
            </aside>
        </div>

        {{-- LATEST NEWS --}}
        @if(!empty($news))
            <section class="space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-black text-white">Berita Terbaru</h2>
                    <a href="{{ route('news.index') }}" class="text-xs font-bold text-emerald-400 hover:underline">Semua berita →</a>
                </div>
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($news as $item)
                        <article class="group flex flex-col overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/80 shadow-lg transition-all hover:border-slate-700 hover:-translate-y-0.5">
                            @if(!empty($item['url_to_image']))
                                <a href="{{ route('news.show', $item['id']) }}" class="block aspect-[16/9] overflow-hidden bg-slate-950">
                                    <img src="{{ $item['url_to_image'] }}" alt="" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" loading="lazy" onerror="this.closest('a').style.display='none'">
                                </a>
                            @endif
                            <div class="flex flex-1 flex-col p-4">
                                <span class="mb-2 inline-flex w-fit items-center rounded-md bg-emerald-500/10 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-emerald-400 border border-emerald-500/20">{{ $item['source'] ?? '—' }}</span>
                                <h3 class="mb-2 font-extrabold leading-snug text-slate-100">
                                    <a href="{{ route('news.show', $item['id']) }}" class="transition-colors group-hover:text-emerald-400">{{ \Illuminate\Support\Str::limit($item['title'] ?? 'Tanpa judul', 90) }}</a>
                                </h3>
                                <time class="mt-auto font-mono text-[11px] text-slate-500">{{ isset($item['published_at']) ? \Illuminate\Support\Carbon::parse($item['published_at'])->setTimezone('Asia/Jakarta')->locale('id')->translatedFormat('d M Y • H:i') . ' WIB' : '' }}</time>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection
