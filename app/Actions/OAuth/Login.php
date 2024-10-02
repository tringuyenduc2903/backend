<?php

namespace App\Actions\OAuth;

use App\Models\Customer;
use Laravel\Socialite\Contracts\User;

class Login
{
    public static function handle(Customer $customer, User $user, string $provider_name): void
    {
        $customer->socials()->createOrFirst([
            'provider_id' => $user->getId(),
            'provider_name' => $provider_name,
        ]);

        if (
            $user->getEmail() === $customer->email &&
            ! $customer->hasVerifiedEmail()
        ) {
            $customer->markEmailAsVerified();
        }

        auth()->login($customer);
    }
}
