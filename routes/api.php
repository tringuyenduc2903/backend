<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('user/profile-information', fn (): Customer => fortify_user());

    Route::apiResources([
        'address' => AddressController::class,
        'identification' => IdentificationController::class,
        'cart' => CartController::class,
        'review-customer' => ReviewCustomerController::class,
    ]);

    Route::post('review-customer/image', ReviewImageController::class);

    Route::apiResource('social', SocialController::class)
        ->except('store', 'update');

    Route::apiResources([
        'wishlist' => WishlistController::class,
        'order' => OrderController::class,
        'order-motorcycle' => OrderMotorcycleController::class,
    ], [
        'except' => 'update',
    ]);

    Route::post('fee', OrderFeeController::class);
    Route::post('fee-motorcycle', OrderMotorcycleFeeController::class);
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

Route::prefix('review-product/{product}')->group(function () {
    Route::get('/', [ReviewProductController::class, 'index'])
        ->name('review-product.index');
    Route::get('filter', ReviewFilterController::class);
});

Route::apiResource('branch', BranchController::class)
    ->only('index', 'show');

Route::get('setting/{setting_type}', SettingController::class);

Route::post('ghn', GhnController::class);

Route::post('pay-os/{order_type}', PayOsController::class);

require __DIR__.'/auth.php';
