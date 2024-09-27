<?php

namespace App\Listeners;

use App\Events\AdminOrderCreatedEvent;
use App\Mail\AdminOrderCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendAdminOrderCreatedNotification implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(AdminOrderCreatedEvent $event): void
    {
        Mail::to($event->order->customer)->send(
            app(AdminOrderCreated::class, [
                'order' => $event->order,
                'employee' => $event->employee,
            ])
        );
    }
}
