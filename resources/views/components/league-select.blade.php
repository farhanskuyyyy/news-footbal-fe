@props(['id', 'name', 'leagues', 'selected' => null, 'autoSubmit' => false])
@php
    $choice = collect($leagues)->firstWhere('id', $selected) ?? collect($leagues)->first();
    $image = $choice['image_path'] ?? null;
@endphp
<div class="league-select" data-league-picker>
    <span class="entity-mark league-select-mark" aria-hidden="true">
        <img @if($image) src="{{ $image }}" @else hidden @endif alt="">
        <x-icon name="shield" class="h-5 w-5 text-muted" />
    </span>
    <select id="{{ $id }}" name="{{ $name }}" data-league-select @if($autoSubmit) onchange="this.form.requestSubmit()" @endif class="w-full rounded-lg border border-control bg-surface text-sm">
        @foreach($leagues as $league)
            <option value="{{ $league['id'] }}" data-image="{{ $league['image_path'] ?? '' }}" @selected(($choice['id'] ?? null) == $league['id'])>{{ $league['name'] }}</option>
        @endforeach
    </select>
</div>
