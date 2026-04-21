<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class LanguagePreferenceController extends Controller
{
    public function show(Request $request)
    {
        $availableLocales = config('app.available_locales', ['id', 'en']);
        $user = $request->user();
        $locale = $user?->getResolvedLocale() ?: app()->getLocale();

        if (!in_array($locale, $availableLocales, true)) {
            $locale = config('app.locale', 'id');
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'locale' => $locale,
                'available_locales' => $availableLocales,
            ],
        ]);
    }

    public function update(Request $request)
    {
        $availableLocales = config('app.available_locales', ['id', 'en']);

        $validated = $request->validate([
            'locale' => 'required|in:' . implode(',', $availableLocales),
        ]);

        $user = $request->user();

        if ($user && User::supportsPreferredLocale()) {
            $user->update([
                'preferred_locale' => $validated['locale'],
            ]);
        }

        if ($request->hasSession()) {
            $request->session()->put('locale', $validated['locale']);
        }

        app()->setLocale($validated['locale']);

        return response()->json([
            'status' => 'success',
            'message' => 'Language preference updated successfully.',
            'data' => [
                'locale' => $validated['locale'],
                'available_locales' => $availableLocales,
            ],
        ]);
    }
}
