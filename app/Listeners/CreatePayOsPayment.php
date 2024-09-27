<?php

namespace App\Listeners;

use App\Enums\OrderPaymentMethod;
use App\Events\AdminOrderCreatedEvent;
use App\Facades\PayOS;
use App\Models\OrderProduct;
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
        if ($event->order->payment_method != OrderPaymentMethod::BANK_TRANSFER) {
            return;
        }

        $data = [
            'orderCode' => $event->order->id,
            'amount' => (int)$event->order->total,
            'description' => trans('Order payment Id #:number', [
                'number' => $event->order->id,
            ]),
            'buyerName' => $event->order->address->customer_name,
            'buyerEmail' => $event->order->customer->email,
            'buyerPhone' => $event->order->address->customer_phone_number,
            'buyerAddress' => $event->order->address->address_preview,
            'items' => $event->order->options
                ->map(fn(OrderProduct $order_product): array => [
                    'name' => $order_product->option->product->name,
                    'quantity' => $order_product->amount,
                    'price' => (int)$order_product->price,
                ])
                ->toArray(),
            'cancelUrl' => route('orders.show', ['id' => $event->order->id]),
            'returnUrl' => route('orders.show', ['id' => $event->order->id]),
        ];

        $response = PayOS::createPaymentLink($data);

        $event->order
            ->forceFill([
                'payment_checkout_url' => $response['checkoutUrl'],
            ])
            ->save();
    }
}
