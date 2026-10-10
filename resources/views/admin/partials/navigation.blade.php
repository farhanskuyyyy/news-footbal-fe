<div class="cms-navigation-content">
    <a href="{{ route('admin.dashboard') }}" class="mb-8 flex min-h-11 items-center gap-2 text-xl font-bold tracking-tight">
        <img src="{{ asset('favicon.svg') }}" alt="" width="28" height="28">
        PitchTicker
    </a>
    <p class="mb-4 text-sm font-semibold">{{ __('cms.workspace') }}</p>
    <nav aria-label="{{ __('cms.navigation') }}" class="space-y-6">
        <a href="{{ route('admin.dashboard') }}" @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif class="cms-nav-link">{{ __('cms.overview') }}</a>
        @foreach(['content' => ['users.index' => 'users', 'news.index' => 'news', 'leagues.index' => 'leagues'], 'operations' => ['scrapers' => 'scrapers', 'sync' => 'sync']] as $group => $items)
            <div>
                <p class="mb-1 px-3 text-xs text-muted">{{ __('cms.'.$group) }}</p>
                @foreach($items as $route => $label)
                    <a href="{{ route('admin.'.$route) }}" @if(request()->routeIs('admin.'.str_replace('.index', '.*', $route))) aria-current="page" @endif class="cms-nav-link">{{ __('cms.'.$label) }}</a>
                @endforeach
            </div>
        @endforeach
    </nav>
    <div class="mt-auto space-y-2 border-t border-line pt-5">
        <a href="{{ route('home') }}" class="cms-nav-link text-muted">{{ __('cms.site') }}</a>
        <button type="button" hidden data-theme-toggle aria-pressed="false" class="cms-nav-link w-full text-left">{{ __('cms.theme') }}</button>
        <div class="flex min-h-11 items-center justify-between gap-2 px-3 text-sm">
            <span class="text-muted">{{ __('cms.language') }}</span>
            <div class="flex gap-2">
                @foreach(['id' => 'ID', 'en' => 'EN'] as $locale => $label)
                    <a href="{{ route('locale.switch', $locale) }}" lang="{{ $locale }}" @if(app()->getLocale() === $locale) aria-current="true" @endif class="flex min-h-11 min-w-11 items-center justify-center font-semibold {{ app()->getLocale() === $locale ? 'text-link underline underline-offset-4' : 'text-muted' }}">{{ $label }}</a>
                @endforeach
            </div>
        </div>
        <div class="border-t border-line px-3 pt-4">
            <p class="break-words text-sm font-semibold">{{ auth()->user()->name }}</p>
            <p class="mt-1 break-all text-xs text-muted">{{ auth()->user()->email }}</p>
            <form method="POST" action="{{ route('logout') }}" class="mt-2">@csrf<button class="min-h-11 text-sm text-muted hover:text-foreground">{{ __('common.actions.logout') }}</button></form>
        </div>
    </div>
</div>
