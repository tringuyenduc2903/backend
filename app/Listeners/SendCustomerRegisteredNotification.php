<?php

namespace App\Listeners;

use App\Mail\CustomerRegistered;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendCustomerRegisteredNotification implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        Mail::to($event->user)->send(
            app(CustomerRegistered::class, [
                'customer' => $event->user,
            ])
        );
    }
}
