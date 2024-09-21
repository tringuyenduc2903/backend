<?php

namespace App\Observers;

use App\Actions\HandleOrderPrice;
use App\Enums\OrderShippingType;
use App\Enums\OrderStatus;
use App\Models\Order;

class StoreOrderObserver
{
    /**
     * Handle the Order "creating" event.
     */
    public function creating(Order $order): void
    {
        $order->status = match ($order->shipping_type) {
            OrderShippingType::PICKUP_AT_STORE => OrderStatus::TO_PAY,
            OrderShippingType::DOOR_TO_DOOR_DELIVERY => OrderStatus::TO_SHIP,
            default => OrderStatus::CANCELLED,
        };

        $price_quote = app(HandleOrderPrice::class)->getPriceQuote();

        $order->tax = $price_quote['tax'];
        $order->shipping_fee = $price_quote['shipping_fee'];
        $order->handling_fee = $price_quote['handling_fee'];
        $order->total = $price_quote['total'];
    }
}
