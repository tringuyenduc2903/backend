<?php

namespace App\Providers;

use App\Enums\EmployeePermissionEnum;
use App\Models\Customer;
use App\Models\Employee;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class SwaggerUiServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::define(
            'viewSwaggerUI',
            fn (?Customer $customer, ?Employee $employee): bool => $employee && $employee->hasPermissionTo(
                EmployeePermissionEnum::API_DOCS,
                config('backpack.base.guard')
            )
        );
    }
}
