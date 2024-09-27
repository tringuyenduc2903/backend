<?php

namespace App\Listeners;

use App\Enums\OrderPaymentMethod;
use App\Events\OrderCreatedEvent;
use App\Models\OrderProduct;
use Exception;
use PayOS\PayOS;

class CreatePayOsPayment
{
    /**
     * Handle the event.
     *
     * @throws Exception
     */
    public function handle(OrderCreatedEvent $event): void
    {
        if ($event->order->payment_method != OrderPaymentMethod::BANK_TRANSFER) {
            return;
        }

        $data = [
            'orderCode' => $event->order->id,
            'amount' => (int) $event->order->total,
            'description' => trans('Order payment Id #:number', [
                'number' => $event->order->id,
            ]),
            'buyerName' => $event->order->address->customer_name,
            'buyerEmail' => $event->order->customer->email,
            'buyerPhone' => $event->order->address->customer_phone_number,
            'buyerAddress' => $event->order->address->address_preview,
            'items' => $event->order->options
                ->map(fn (OrderProduct $order_product): array => [
                    'name' => $order_product->option->product->name,
                    'quantity' => $order_product->amount,
                    'price' => (int) $order_product->price,
                ])
                ->toArray(),
            'cancelUrl' => route('orders.show', ['id' => $event->order->id]),
            'returnUrl' => route('orders.show', ['id' => $event->order->id]),
        ];

        $pay_os = app(PayOS::class, [
            'clientId' => config('services.payos.client_id'),
            'apiKey' => config('services.payos.client_secret'),
            'checksumKey' => config('services.payos.checksum'),
            'partnerCode' => config('services.payos.partner_code'),
        ]);

        $response = $pay_os->createPaymentLink($data);

        $event->order
            ->forceFill([
                'payment_checkout_url' => $response['checkoutUrl'],
            ])
            ->save();

        if (app()->environment('local')) {
            return;
        }

        $pay_os->confirmWebhook(route('pay_os'));
    }
}
