<?php

namespace App\Providers;

use App\Models\Address;
use App\Models\Customer;
use App\Observers\CreateCustomer;
use App\Observers\StoreAddress;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return sprintf(
                '%s/password-reset/%s?email=%s',
                config('app.frontend_url'),
                $token,
                $notifiable->getEmailForPasswordReset()
            );
        });

        Address::observe(
            StoreAddress::class,
        );

        Customer::observe(
            CreateCustomer::class,
        );
    }
}
