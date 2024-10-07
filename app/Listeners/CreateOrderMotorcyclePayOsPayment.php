<?php

namespace App\Listeners;

use App\Enums\OrderPaymentMethod;
use App\Enums\OrderTransactionStatus;
use App\Events\AdminOrderMotorcycleCreatedEvent;
use App\Facades\PayOsOrderMotorcycleApi;
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
        if ($event->order_motorcycle->payment_method == OrderPaymentMethod::BANK_TRANSFER) {
            $response = PayOsOrderMotorcycleApi::createPaymentLink($event->order_motorcycle);

            $event->order_motorcycle
                ->forceFill([
                    'payment_checkout_url' => $response['checkoutUrl'],
                ])
                ->save();

            $event->order_motorcycle->transactions()->create([
                'amount' => $event->order_motorcycle->total,
                'status' => OrderTransactionStatus::PENDING,
                'reference' => $response['paymentLinkId'],
            ]);
        }
    }
}
