<?php

namespace App\Listeners;

use App\Enums\OrderPaymentMethod;
use App\Events\AdminOrderMotorcycleCreatedEvent;
use App\Facades\PayOSOrderMotorcycle;
use Exception;

class CreateOrderMotorcyclePayOsPayment
{
    /**
     * Handle the event.
     *
     * @throws Exception
     */
    public function handle($event): void
    {
        /** @var AdminOrderMotorcycleCreatedEvent $event */
        if ($event->order_motorcycle->payment_method != OrderPaymentMethod::BANK_TRANSFER) {
            return;
        }

        $response = PayOSOrderMotorcycle::createPaymentLink($event->order_motorcycle);

        $event->order_motorcycle
            ->forceFill([
                'payment_checkout_url' => $response['checkoutUrl'],
            ])
            ->save();
    }
}
