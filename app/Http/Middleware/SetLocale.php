<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $availableLocales = config('app.available_locales', ['id', 'en']);
        $locale = null;

        if ($request->hasSession()) {
            $locale = $request->session()->get('locale');
        }

        if (!$locale) {
            $locale = $request->header('X-Locale');
        }

        if (!$locale) {
            $locale = $request->query('locale');
        }

        if (!$locale) {
            $user = $request->user();

            if (!$user && $request->bearerToken()) {
                $accessToken = PersonalAccessToken::findToken($request->bearerToken());
                $user = $accessToken?->tokenable;
            }

            if ($user && User::supportsPreferredLocale()) {
                $locale = $user->preferred_locale;
            }
        }

        if (!$locale) {
            $preferred = $request->getPreferredLanguage($availableLocales);
            $locale = $preferred ?: config('app.locale');
        }

        if (!in_array($locale, $availableLocales, true)) {
            $locale = config('app.locale');
        }

        App::setLocale($locale);

        return $next($request);
    }
}
