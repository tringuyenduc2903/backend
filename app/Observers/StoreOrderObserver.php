<?php

namespace App\Observers;

use App\Actions\OrderPrice;
use App\Enums\OrderShippingMethod;
use App\Enums\OrderStatus;
use App\Models\Order;

class StoreOrderObserver
{
    /**
     * Handle the Order "creating" event.
     */
    public function creating(Order $order): void
    {
        $order->status = match ((int) $order->shipping_method) {
            OrderShippingMethod::PICKUP_AT_STORE => OrderStatus::TO_PAY,
            OrderShippingMethod::DOOR_TO_DOOR_DELIVERY => OrderStatus::TO_SHIP,
            default => OrderStatus::CANCELLED,
        };

        $price_quote = app(OrderPrice::class, [
            'options' => request('options'),
            'shipping_method' => $order->shipping_method,
            'address_id' => $order->address->id,
        ])->getPriceQuote();

        $order->tax = $price_quote['tax'];
        $order->shipping_fee = $price_quote['shipping_fee'];
        $order->handling_fee = $price_quote['handling_fee'];
        $order->total = $price_quote['total'];
    }
}
