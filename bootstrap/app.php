<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(\Illuminate\Http\Middleware\HandleCors::class);
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);

        $middleware->api(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);

        // 1. Daftarkan Alias Middleware RoleCheck agar bisa dipanggil sebagai 'role'
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleCheck::class,
        ]);

        // 2. Agar Sanctum bisa handle session/cookie antara React & Laravel
        $middleware->statefulApi();

        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        // 3. Pengecualian CSRF untuk API
        $middleware->validateCsrfTokens(except: [
            'api/register',
            'api/login',
            'api/forgot-password',
            'api/forgot-password/validate-token',
            'api/reset-password',
            'api/auth/google',
            'api/auth/google/callback',
            'api/auth/google/token',
            'api/intern/update-profile',
            'api/intern/start-test',
            'api/intern/submit-test',
            'api/intern/apply',
            'api/company/jobs*',
            'api/company/profile*',
            'api/company/talent/candidates*',
            'api/admin/profile/*',
            'api/admin/talents*',
            'api/admin/users-management*',
            'api/admin/partners*',
            'api/admin/verification/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
