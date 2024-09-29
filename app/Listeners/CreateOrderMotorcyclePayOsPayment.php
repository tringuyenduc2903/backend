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

        $data = [
            'orderCode' => $event->order_motorcycle->id,
            'amount' => (int) $event->order_motorcycle->total,
            'description' => sprintf('%s: %s', trans('Order'), $event->order_motorcycle->id),
            'buyerName' => $event->order_motorcycle->address->customer_name,
            'buyerEmail' => $event->order_motorcycle->customer->email,
            'buyerPhone' => $event->order_motorcycle->address->customer_phone_number,
            'buyerAddress' => $event->order_motorcycle->address->address_preview,
            'items' => [[
                'name' => $event->order_motorcycle->option->product->name,
                'quantity' => $event->order_motorcycle->amount,
                'price' => (int) $event->order_motorcycle->price,
            ]],
            'cancelUrl' => route('orders.show', ['id' => $event->order_motorcycle->id]),
            'returnUrl' => route('orders.show', ['id' => $event->order_motorcycle->id]),
        ];

        $response = PayOSOrderMotorcycle::createPaymentLink($data);

        $event->order_motorcycle
            ->forceFill([
                'payment_checkout_url' => $response['checkoutUrl'],
            ])
            ->save();
    }
}
