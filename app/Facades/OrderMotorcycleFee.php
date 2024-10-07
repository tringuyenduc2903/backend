<?php

namespace App\Facades;

use App\Actions\MotorcycleFee;
use Illuminate\Support\Facades\Facade;

class OrderMotorcycleFee extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return MotorcycleFee::class;
    }
}
