<?php

namespace App\Listeners;

use App\Events\FrontendOrderCreatedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;

class RemoveProductInCartList implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(FrontendOrderCreatedEvent $event): void
    {
        $event->customer
            ->carts()
            ->whereIn(
                'option_id',
                $event->order->options()->pluck('option_id')->toArray()
            )
            ->delete();
    }
}
