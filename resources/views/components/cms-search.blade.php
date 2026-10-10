@props(['label', 'query' => ''])
<form method="GET" action="{{ url()->current() }}" class="cms-card mb-6 flex flex-wrap items-end gap-2">
    <div class="w-full max-w-md min-w-0">
        <label for="cms-search" class="mb-2 block text-sm font-medium">{{ $label }}</label>
        <input id="cms-search" name="q" type="search" value="{{ $query }}" maxlength="200" class="cms-input" autocomplete="off">
    </div>
    <button class="cms-button">{{ __('cms.search') }}</button>
    @if($query !== '')<a href="{{ url()->current() }}" class="cms-button">{{ __('cms.clear') }}</a>@endif
</form>
