<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class GHNv2 extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return \App\Actions\GHNv2::class;
    }
}
