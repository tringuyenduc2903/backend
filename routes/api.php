<?php

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')
    ->get('user/profile-information', fn (Request $request): Customer => $request->user());

require __DIR__.'/auth.php';
