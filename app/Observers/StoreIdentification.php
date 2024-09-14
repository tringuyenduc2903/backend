<?php

namespace App\Observers;

use App\Models\Customer;
use App\Models\Identification;

class StoreIdentification
{
    /**
     * Handle the Identification "updating" event.
     */
    public function updating(Identification $identification): void
    {
        $this->creating($identification);
    }

    /**
     * Handle the Identification "creating" event.
     */
    public function creating(Identification $identification): void
    {
        if (! $identification->default) {
            return;
        }

        if (backpack_auth()->check()) {
            $user = backpack_user();
        } elseif (fortify_auth()->check()) {
            $user = fortify_auth()->user();
        } else {
            $user = Customer::findOrFail($identification->customer_id);
        }

        $user->identifications()
            ->whereDefault(true)
            ->update(['default' => false]);
    }
}
