<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Route;

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\CRUD.
// Routes you generate using Backpack\Generators will be placed here.

Route::prefix(config('backpack.base.route_prefix', 'admin'))
    ->middleware(array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ))
    ->group(function () {
        Route::crud('employees', EmployeeCrudController::class);
        Route::crud('roles', RoleCrudController::class);
        Route::crud('branches', BranchCrudController::class);
        Route::crud('customers', CustomerCrudController::class);
        Route::crud('settings', SettingCrudController::class);
        Route::crud('products', ProductCrudController::class);
        Route::crud('categories', CategoryCrudController::class);
        Route::crud('motor-cycles', MotorCycleCrudController::class);
    });
