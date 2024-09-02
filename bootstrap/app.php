<?php

use App\Http\Responses\LoginResponse;
use App\Http\Responses\LogoutResponse;
use App\Http\Responses\TwoFactorEnabledResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [
            EnsureFrontendRequestsAreStateful::class,
        ]);
    })
    ->withBindings([
        \Laravel\Fortify\Http\Responses\LoginResponse::class => LoginResponse::class,
        \Laravel\Fortify\Http\Responses\LogoutResponse::class => LogoutResponse::class,
        \Laravel\Fortify\Http\Responses\TwoFactorEnabledResponse::class => TwoFactorEnabledResponse::class,
    ])
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
