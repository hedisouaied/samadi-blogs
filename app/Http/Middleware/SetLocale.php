<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class SetLocale
{
    public const SESSION_KEY = 'locale';

    public const COOKIE_NAME = 'locale';

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolve($request);

        app()->setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }

    protected function resolve(Request $request): string
    {
        $supported = array_keys(supported_locales());

        $candidates = [
            $request->hasSession() ? $request->session()->get(self::SESSION_KEY) : null,
            $request->cookie(self::COOKIE_NAME),
            $this->defaultLocale(),
            config('app.locale'),
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && in_array($candidate, $supported, true)) {
                return $candidate;
            }
        }

        return 'en';
    }

    protected function defaultLocale(): ?string
    {
        try {
            return (string) setting('general.default_language');
        } catch (Throwable) {
            return null;
        }
    }
}
