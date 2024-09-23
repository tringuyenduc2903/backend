<?php

namespace App\Observers;

use App\Mail\CustomerCreated;
use App\Mail\CustomerRegistered;
use App\Models\Customer;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CreateCustomer
{
    /**
     * Handle the Customer "creating" event.
     */
    public function creating(Customer $customer): void
    {
        if ($customer->password) {
            Mail::to($customer)->send(
                app(CustomerRegistered::class, [
                    'customer' => $customer,
                ])
            );

            return;
        }

        $customer->password = $password = Str::password(20);

        Mail::to($customer)->send(
            app(CustomerCreated::class, [
                'customer' => $customer,
                'admin' => backpack_user(),
                'password' => $password,
            ])
        );
    }
}
