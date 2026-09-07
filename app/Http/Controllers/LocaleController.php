<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\RedirectResponse;

class LocaleController extends Controller
{
    /**
     * Stores the chosen UI language in the session and returns the visitor to
     * the page they came from. Unknown locales are ignored rather than
     * rejected — the switcher only ever links to supported ones.
     */
    public function switch(string $locale): RedirectResponse
    {
        if (array_key_exists($locale, SetLocale::SUPPORTED)) {
            session(['locale' => $locale]);
        }

        return redirect()->back();
    }
}
