<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('user/profile-information', fn (Request $request): Customer => $request->user());

    Route::apiResources([
        'address' => AddressController::class,
        'identification' => IdentificationController::class,
    ]);

    Route::apiResource('social', SocialController::class)
        ->only(['index', 'show']);
});

Route::apiResource('province', ProvinceController::class)
    ->only('index');
Route::apiResources([
    'district' => DistrictController::class,
    'ward' => WardController::class,
], [
    'only' => 'show',
]);

require __DIR__.'/auth.php';
