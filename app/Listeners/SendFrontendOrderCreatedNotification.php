<?php

namespace App\Listeners;

use App\Events\FrontendOrderCreatedEvent;
use App\Mail\FrontendOrderCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendFrontendOrderCreatedNotification implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(FrontendOrderCreatedEvent $event): void
    {
        Mail::to($event->order->customer)->send(
            app(FrontendOrderCreated::class, [
                'order' => $event->order,
            ])
        );
    }
}
