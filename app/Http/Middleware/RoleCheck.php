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

        // Jika request membawa bearer token, prioritaskan user dari token
        // dibanding session/cookie browser agar tidak tertukar dengan login
        // tab lain pada origin yang sama.
        if ($request->bearerToken()) {
            $accessToken = PersonalAccessToken::findToken($request->bearerToken());
            $tokenUser = $accessToken?->tokenable;

            if ($tokenUser) {
                $user = $tokenUser;
                $request->setUserResolver(static fn () => $tokenUser);
                Auth::setUser($tokenUser);
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
