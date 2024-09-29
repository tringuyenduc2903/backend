<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Models\OrderMotorcycle;

class StoreOrderMotorcycleObserver
{
    /**
     * Handle the OrderMotorcycle "creating" event.
     */
    public function creating(OrderMotorcycle $order_motorcycle): void
    {
        $order_motorcycle->status = OrderStatus::TO_PAY;
        $order_motorcycle->amount = 1;

        $order_motorcycle->price = $order_motorcycle->option->price;
        $order_motorcycle->value_added_tax = $order_motorcycle->option->value_added_tax;

        $fee = session()->pull('order-motorcycle.fee');

        $order_motorcycle->motorcycle_registration_support_fee = $fee['motorcycle_registration_support_fee'];
        $order_motorcycle->registration_fee = $fee['registration_fee'];
        $order_motorcycle->license_plate_registration_fee = $fee['license_plate_registration_fee'];
        $order_motorcycle->tax = $fee['tax'];
        $order_motorcycle->handling_fee = $fee['handling_fee'];
        $order_motorcycle->total = $fee['total'];
    }

    /**
     * Handle the OrderMotorcycle "created" event.
     */
    public function created(OrderMotorcycle $order_motorcycle): void
    {
        $order_motorcycle->option->update([
            'quantity' => $order_motorcycle->option->quantity - $order_motorcycle->amount,
        ]);
    }
}
