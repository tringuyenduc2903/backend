<?php

namespace App\Listeners;

use App\Enums\OrderShippingMethod;
use App\Enums\OrderStatus;
use App\Events\AdminOrderCreatedEvent;
use App\Facades\Ghn;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreateOrderGhnShip implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle($event): void
    {
        /** @var AdminOrderCreatedEvent $event */
        if (
            $event->order->status != OrderStatus::TO_SHIP ||
            $event->order->shipping_method != OrderShippingMethod::DOOR_TO_DOOR_DELIVERY
        ) {
            return;
        }

        $response = Ghn::createOrder($event->order);

        $event->order
            ->forceFill([
                'shipping_code' => $response['order_code'],
            ])
            ->save();
    }
}
