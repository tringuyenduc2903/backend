<?php

namespace App\Observers;

use App\Enums\OptionStatus;
use App\Models\Option;

class OptionObserver
{
    /**
     * Handle the Option "saving" event.
     */
    public function saving(Option $option): void
    {
        if (
            $option->quantity == 0 &&
            $option->status == OptionStatus::IN_STOCK
        ) {
            $option->status = OptionStatus::OUT_OF_STOCK;
        }
    }
}
