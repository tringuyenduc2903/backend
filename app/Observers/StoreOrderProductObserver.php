<?php

namespace App\Observers;

use App\Models\OrderProduct;

class StoreOrderProductObserver
{
    /**
     * Handle the OrderProduct "creating" event.
     */
    public function creating(OrderProduct $order_product): void
    {
        $option = $order_product->option;

        $order_product->price = $option->price;
        $order_product->value_added_tax = $option->value_added_tax;
    }

    /**
     * Handle the OrderProduct "created" event.
     */
    public function created(OrderProduct $order_product): void
    {
        $option = $order_product->option;

        $option->update([
            'quantity' => $option->quantity - $order_product->amount,
        ]);
    }
}
