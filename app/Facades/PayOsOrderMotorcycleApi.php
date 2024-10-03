<?php

namespace App\Facades;

use App\Api\PayOs\PayOsOrderMotorcycle;
use Illuminate\Support\Facades\Facade;

class PayOsOrderMotorcycleApi extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return PayOsOrderMotorcycle::class;
    }
}
