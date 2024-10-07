<?php

namespace App\Listeners;

use App\Enums\OrderPaymentMethod;
use App\Enums\OrderTransactionStatus;
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
        if ($event->order->payment_method == OrderPaymentMethod::BANK_TRANSFER) {
            $response = PayOsApi::createPaymentLink(
                $event->order,
                $event?->cancel_url,
                $event?->return_url,
            );

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
