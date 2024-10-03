<?php

namespace App\Facades;

use App\Api\PayOs\PayOsOrder;
use Illuminate\Support\Facades\Facade;

class PayOsOrderApi extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return PayOsOrder::class;
    }
}
