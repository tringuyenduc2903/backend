<?php

use App\Http\Controllers\OAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

Route::prefix('auth')
    ->middleware('guest')
    ->group(function () {
        Route::get('redirect/{driver_name}', [OAuthController::class, 'redirect'])
            ->name('auth.redirect');

        Route::get('callback/{driver_name}', [OAuthController::class, 'callback']);
    });
