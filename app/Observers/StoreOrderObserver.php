<?php

namespace App\Observers;

use App\Enums\OrderPaymentMethod;
use App\Enums\OrderShippingMethod;
use App\Enums\OrderStatus;
use App\Facades\Ghn;
use App\Facades\PayOSOrder;
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

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        if ($order->status == OrderStatus::CANCELLED) {
            if (
                $order->shipping_method == OrderShippingMethod::DOOR_TO_DOOR_DELIVERY &&
                $order->shipping_code
            ) {
                Ghn::cancelOrder($order);
            }

            if (
                $order->payment_method == OrderPaymentMethod::BANK_TRANSFER &&
                $order->payment_checkout_url
            ) {
                PayOSOrder::cancelPaymentLink($order);
            }
        }
    }
}
