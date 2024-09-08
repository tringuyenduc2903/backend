<?php

namespace App\Providers;

use App\Models\Address;
use App\Models\Customer;
use App\Models\Identification;
use App\Models\MotorCycle;
use App\Observers\CreateCustomer;
use App\Observers\StoreAddress;
use App\Observers\StoreIdentification;
use App\Observers\StoreMotorCycle;
use App\Rules\Action;
use App\Rules\Image;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Validator;
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

        Customer::observe(
            CreateCustomer::class,
        );

        Address::observe(
            StoreAddress::class,
        );

        Identification::observe(
            StoreIdentification::class,
        );

        MotorCycle::observe(
            StoreMotorCycle::class,
        );

        Validator::extend(
            'actions',
            fn ($attribute, $value, $parameters, $validator): bool => Action::extends($attribute, $value, $validator)
        );

        Validator::extend(
            'image_banner',
            fn ($attribute, $value, $parameters, $validator): bool => Image::extends($attribute, $value, $validator)
        );
    }
}
