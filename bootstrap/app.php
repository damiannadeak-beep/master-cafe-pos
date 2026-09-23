<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
        
        // TAMBAHKAN KODE INI UNTUK SPATIE PERMISSION
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'ensure_shift_open' => \App\Http\Middleware\EnsureShiftOpen::class,
        ]);

        $middleware->redirectGuestsTo(function ($request) {
            $ownerSlug = config('auth.owner_path', 'ruang-owner-x92k');
            $kasirSlug = config('auth.kasir_path', 'pos-kasir-gate-88');

            if ($request->is('admin*') || $request->is($ownerSlug . '*')) {
                return route('owner.login');
            }
            if ($request->is('kasir*') || $request->is($kasirSlug . '*')) {
                return route('kasir.login');
            }
            return '/';
        });

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();