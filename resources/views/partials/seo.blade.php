@php
    $seo = app(\App\Services\SeoService::class)->metadata($seoView, get_defined_vars(), trim($__env->yieldContent('title', __('landing.title'))));
@endphp
<title>{{ $seo['title'] }}</title>
<meta name="description" content="{{ $seo['description'] }}">
<meta name="robots" content="{{ $seo['indexable'] ? 'index, follow, max-image-preview:large' : 'noindex, follow' }}">
<link rel="canonical" href="{{ $seo['canonical'] }}">
<meta property="og:type" content="website">
<meta property="og:site_name" content="PitchTicker">
<meta property="og:title" content="{{ $seo['title'] }}">
<meta property="og:description" content="{{ $seo['description'] }}">
<meta property="og:url" content="{{ $seo['canonical'] }}">
<meta property="og:locale" content="{{ app()->getLocale() === 'id' ? 'id_ID' : 'en_US' }}">
<meta name="twitter:card" content="{{ $seo['image'] ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $seo['title'] }}">
<meta name="twitter:description" content="{{ $seo['description'] }}">
@if($seo['image'])
<meta property="og:image" content="{{ $seo['image'] }}">
<meta name="twitter:image" content="{{ $seo['image'] }}">
@endif
@if(config('seo.google_verification'))
<meta name="google-site-verification" content="{{ config('seo.google_verification') }}">
@endif
<script type="application/ld+json">{!! json_encode($seo['schema'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
