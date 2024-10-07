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
        Mail::to($event->order->customer)->send(
            app(AdminOrderMotorcycleCreated::class, [
                'order' => $event->order,
                'employee' => $event->employee,
            ])
        );
    }
}
