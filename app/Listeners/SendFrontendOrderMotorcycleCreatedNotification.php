<?php

namespace App\Listeners;

use App\Events\FrontendOrderMotorcycleCreatedEvent;
use App\Mail\FrontendOrderMotorcycleCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendFrontendOrderMotorcycleCreatedNotification implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(FrontendOrderMotorcycleCreatedEvent $event): void
    {
        Mail::to($event->order_motorcycle->customer)->send(
            app(FrontendOrderMotorcycleCreated::class, [
                'order_motorcycle' => $event->order_motorcycle,
            ])
        );
    }
}
