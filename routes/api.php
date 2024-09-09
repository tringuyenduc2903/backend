<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')
    ->get('user/profile-information', fn (Request $request): Customer => $request->user());

Route::apiResource('province', ProvinceController::class)
    ->only('index');
Route::apiResource('district', DistrictController::class)
    ->only('show');

require __DIR__.'/auth.php';
