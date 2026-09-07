<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Locales the UI ships translations for. The first entry is the default
     * used whenever the session holds nothing (or something unrecognised).
     *
     * @var array<string, string>
     */
    public const SUPPORTED = [
        'en' => 'English',
        'id' => 'Bahasa Indonesia',
    ];

    /**
     * Applies the locale stored in the session to both the translator and
     * Carbon, so `translatedFormat()` follows the chosen language too.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale');

        if (! is_string($locale) || ! array_key_exists($locale, self::SUPPORTED)) {
            $locale = config('app.locale', 'en');
        }

        App::setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
