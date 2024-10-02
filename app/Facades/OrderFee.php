<?php

namespace App\Facades;

use App\Actions\Fee\Order;
use Illuminate\Support\Facades\Facade;

class OrderFee extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return Order::class;
    }
}
