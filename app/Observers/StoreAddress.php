<?php

namespace App\Observers;

use App\Models\Address;
use App\Models\Customer;

class StoreAddress
{
    /**
     * Handle the Address "updating" event.
     */
    public function updating(Address $address): void
    {
        $this->creating($address);
    }

    /**
     * Handle the Address "creating" event.
     */
    public function creating(Address $address): void
    {
        if (! $address->default) {
            return;
        }

        if (backpack_auth()->check()) {
            $user = backpack_user();
        } elseif (auth('sanctum')->check()) {
            $user = auth('sanctum')->user();
        } else {
            $user = Customer::findOrFail($address->customer_id);
        }

        $user->addresses()
            ->whereDefault(true)
            ->update(['default' => false]);
    }
}
