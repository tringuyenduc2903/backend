<?php

namespace App\Actions\OAuth;

use App\Models\Customer;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User;

class Register
{
    public function handle(User $user, string $provider_name): void
    {
        $customer = Customer::make([
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'timezone' => array_search(
                config('app.timezone'),
                timezone_identifiers_list()
            ),
        ]);

        $customer->forceFill([
            'password' => Str::password(20),
        ]);

        $customer->save();

        $customer->socials()->updateOrCreate([
            'provider_id' => $user->getId(),
            'provider_name' => $provider_name,
        ]);

        $customer->markEmailAsVerified();

        auth()->login($customer, true);
    }
}
