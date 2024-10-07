<?php

namespace App\Facades;

use App\Api\PayOs\PayOs;
use Illuminate\Support\Facades\Facade;

class PayOsApi extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return PayOs::class;
    }
}
