<?php

namespace App\Observers;

use App\Models\Address;

class AddressObserver
{
    /**
     * Handle the Address "saving" event.
     */
    public function saving(Address $address): void
    {
        if ($address->default) {
            current_user($address->customer_id)->addresses()
                ->whereDefault(true)
                ->update(['default' => false]);
        }
    }
}
