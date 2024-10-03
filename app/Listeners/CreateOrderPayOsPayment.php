<?php

namespace App\Listeners;

use App\Enums\OrderPaymentMethod;
use App\Events\AdminOrderCreatedEvent;
use App\Facades\PayOsOrderApi;
use Exception;

class CreateOrderPayOsPayment
{
    /**
     * Handle the event.
     *
     * @throws Exception
     */
    public function handle($event): void
    {
        /** @var AdminOrderCreatedEvent $event */
        if ($event->order->payment_method == OrderPaymentMethod::BANK_TRANSFER) {
            $response = PayOsOrderApi::createPaymentLink($event->order);

            $event->order
                ->forceFill([
                    'payment_checkout_url' => $response['checkoutUrl'],
                ])
                ->save();
        }
    }
}
