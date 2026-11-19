<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class LocaleController extends Controller
{
    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        abort_unless(array_key_exists($locale, supported_locales()), 404);

        $request->session()->put(SetLocale::SESSION_KEY, $locale);

        Cookie::queue(SetLocale::COOKIE_NAME, $locale, 60 * 24 * 365);

        return redirect()->back(fallback: route('home'));
    }
}
