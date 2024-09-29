<?php

namespace App\Observers;

use App\Enums\OrderPaymentMethod;
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
        $order->status = (
            $order->shipping_method == OrderShippingMethod::DOOR_TO_DOOR_DELIVERY &&
            $order->payment_method == OrderPaymentMethod::PAYMENT_ON_DELIVERY
        )
            ? OrderStatus::TO_SHIP
            : OrderStatus::TO_PAY;

        $fee = session()->pull('order.fee');

        $order->weight = $fee['weight'];
        $order->tax = $fee['tax'];
        $order->shipping_fee = $fee['shipping_fee'];
        $order->handling_fee = $fee['handling_fee'];
        $order->total = $fee['total'];
    }
}
