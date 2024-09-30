<?php

namespace App\Observers;

use App\Models\Identification;

class StoreIdentification
{
    /**
     * Handle the Identification "saving" event.
     */
    public function saving(Identification $identification): void
    {
        if ($identification->default) {
            current_user($identification->customer_id)->identifications()
                ->whereDefault(true)
                ->update(['default' => false]);
        }
    }
}
