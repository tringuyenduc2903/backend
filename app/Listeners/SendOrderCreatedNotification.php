<?php

namespace App\Listeners;

use App\Events\OrderCreatedEvent;
use App\Mail\OrderCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendOrderCreatedNotification implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(OrderCreatedEvent $event): void
    {
        Mail::to($event->order->customer)->send(
            app(OrderCreated::class, [
                'order' => $event->order,
                'employee' => $event->employee,
            ])
        );
    }
}
