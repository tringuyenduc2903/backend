<?php

namespace App\Observers;

use App\Models\OrderProduct;

class OrderProductObserver
{
    /**
     * Handle the OrderProduct "creating" event.
     */
    public function creating(OrderProduct $order_product): void
    {
        $order_product->price = $order_product->option->price;
        $order_product->value_added_tax = $order_product->option->value_added_tax;
    }

    /**
     * Handle the OrderProduct "created" event.
     */
    public function created(OrderProduct $order_product): void
    {
        $order_product->option->update([
            'quantity' => $order_product->option->quantity - $order_product->amount,
        ]);
    }
}
