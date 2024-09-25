<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class GHNv2Cache extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return \App\Actions\GiaoHangNhanh\GHNv2Cache::class;
    }
}
