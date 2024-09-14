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
        'cart' => CartController::class,
        'review-customer' => ReviewCustomerController::class,
    ]);

    Route::apiResource('social', SocialController::class)
        ->except(['store', 'update']);

    Route::apiResource('wishlist', WishlistController::class)
        ->except('update');
});

Route::apiResource('province', ProvinceController::class)
    ->only('index');
Route::apiResources([
    'district' => DistrictController::class,
    'ward' => WardController::class,
], [
    'only' => 'show',
]);

Route::prefix('product/{product_type}')->group(function () {
    Route::get('/', [ProductController::class, 'index'])
        ->name('product.index');
    Route::get('filter', ProductFilterController::class);
    Route::get('{product}', [ProductController::class, 'show'])
        ->name('product.show');
});

Route::prefix('review-product')->group(function () {
    Route::get('{product}', [ReviewProductController::class, 'index'])
        ->name('review-product.index');
});

Route::apiResource('branch', BranchController::class)
    ->only(['index', 'show']);

require __DIR__.'/auth.php';
