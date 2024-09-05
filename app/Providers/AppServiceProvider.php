<?php

namespace App\Providers;

use App\Models\Address;
use App\Models\Customer;
use App\Models\Identification;
use App\Observers\CreateCustomer;
use App\Observers\StoreAddress;
use App\Observers\StoreIdentification;
use App\Rules\Action;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\InvokableValidationRule;

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

        Validator::extend('actions', function (string $attribute, mixed $value, array $parameters, \Illuminate\Contracts\Validation\Validator $validator): bool {
            $rule = InvokableValidationRule::make(app(Action::class))
                ->setValidator($validator);

            $result = $rule->passes($attribute, $value);

            if (! $result) {
                $validator->setCustomMessages([
                    $attribute => Arr::first($rule->message()),
                ]);
            }

            return $result;
        });
    }
}
