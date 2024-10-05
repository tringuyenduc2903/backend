<?php

namespace App\Observers;

use App\Actions\Fee\OrderMotorcycle;
use App\Enums\OrderShippingMethod;
use App\Enums\OrderStatus;
use App\Enums\OrderTransactionStatus;
use App\Models\Order;
use App\Models\OrderTransaction;

class OrderTransactionObserver
{
    /**
     * Handle the OrderTransaction "saved" event.
     */
    public function saved(OrderTransaction $order_transaction): void
    {
        $amount = OrderTransaction::whereOrderableType($order_transaction->orderable_type)
            ->whereOrderableId($order_transaction->orderable_id)
            ->whereStatus(OrderTransactionStatus::SUCCESSFULLY)
            ->sum('amount');

        $order = match ($order_transaction->orderable_type) {
            Order::class => $order_transaction->order,
            OrderMotorcycle::class => $order_transaction->order_motorcycle,
        };

        if ($amount >= $order->total) {
            $order
                ->forceFill([
                    'status' => $order->shipping_method == OrderShippingMethod::PICKUP_AT_STORE
                        ? OrderStatus::TO_RECEIVE
                        : OrderStatus::TO_SHIP,
                ])
                ->save();
        }
    }
}
