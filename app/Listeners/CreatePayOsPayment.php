<?php

namespace App\Listeners;

use App\Enums\OrderPaymentMethod;
use App\Enums\OrderTransactionStatus;
use App\Events\AdminOrderCreatedEvent;
use App\Facades\PayOsApi;
use Exception;

class CreatePayOsPayment
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
            $response = PayOsApi::createPaymentLink($event->order);

            $event->order
                ->forceFill([
                    'payment_checkout_url' => $response['checkoutUrl'],
                ])
                ->save();

            $event->order->transactions()->create([
                'amount' => $event->order->total,
                'status' => OrderTransactionStatus::PENDING,
                'reference' => $response['paymentLinkId'],
            ]);
        }
    }
}
