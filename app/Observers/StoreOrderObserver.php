<?php

namespace App\Observers;

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

        $fee = session()->pull('order.fee');

        $order->weight = $fee['weight'];
        $order->tax = $fee['tax'];
        $order->shipping_fee = $fee['shipping_fee'];
        $order->handling_fee = $fee['handling_fee'];
        $order->total = $fee['total'];
    }
}
