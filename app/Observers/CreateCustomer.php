<?php

namespace App\Observers;

use App\Mail\CustomerRegistered;
use App\Models\Customer;
use Illuminate\Support\Facades\Mail;

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
        }
    }
}
