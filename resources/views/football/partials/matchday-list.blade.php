@php $today = now('Asia/Jakarta')->format('Y-m-d'); @endphp
<div id="matchday-list" data-date="{{ $date }}" data-heading="{{ $date === $today ? __('football.matchday.today') : \Illuminate\Support\Carbon::parse($date)->locale(app()->getLocale())->translatedFormat('l, d F Y') }}" data-count="{{ ($footballUnavailable ?? false) && !count($fixtures) ? '—' : trans_choice('football.matchday.count', count($fixtures), ['count' => count($fixtures)]) }}">
    @if($footballUnavailable ?? false)<p role="alert" class="mb-3 rounded-lg border border-danger bg-surface p-3 text-sm text-danger">{{ __('common.empty.unavailable') }}</p>@endif
    @include('football.partials.match-groups', ['fixtures' => $fixtures])
</div>
