<?php

namespace App\Listeners;

use App\Events\AdminOrderMotorcycleCreatedEvent;
use App\Mail\AdminOrderMotorcycleCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendAdminOrderMotorcycleCreatedNotification implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(AdminOrderMotorcycleCreatedEvent $event): void
    {
        Mail::to($event->order_motorcycle->customer)->send(
            app(AdminOrderMotorcycleCreated::class, [
                'order_motorcycle' => $event->order_motorcycle,
                'employee' => $event->employee,
            ])
        );
    }
}
