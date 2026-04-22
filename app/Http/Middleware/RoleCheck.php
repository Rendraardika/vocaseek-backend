<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class RoleCheck
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        // Fallback untuk request SPA yang mengirim bearer token tetapi user
        // belum ter-resolve oleh guard saat middleware role dijalankan.
        if (! $user && $request->bearerToken()) {
            $accessToken = PersonalAccessToken::findToken($request->bearerToken());
            $user = $accessToken?->tokenable;

            if ($user) {
                $request->setUserResolver(static fn () => $user);
                Auth::setUser($user);
            }
        }

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if (!in_array($user->role, $roles, true)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses Ditolak!',
                'required_roles' => $roles,
                'current_role' => $user->role,
            ], 403);
        }

        return $next($request);
    }
}
