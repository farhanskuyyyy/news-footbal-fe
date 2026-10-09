@props(['href' => null])
@if($href)
    <a href="{{ $href }}" {{ $attributes }}>{{ $slot }}</a>
@else
    <span {{ $attributes }}>{{ $slot }}</span>
@endif
