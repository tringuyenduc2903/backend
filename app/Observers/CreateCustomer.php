<?php

namespace App\Observers;

use App\Mail\CustomerCreated;
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
        if (backpack_auth()->guest() || fortify_auth()->check()) {
            return;
        }

        $customer->password = $password = Str::password(20);

        $mail = app(CustomerCreated::class, [
            'customer' => $customer,
            'admin' => backpack_user(),
            'password' => $password,
        ]);

        Mail::to($customer)->send($mail);
    }
}
