<?php

namespace App\Facades;

use App\Api\GiaoHangNhanh\Ghn;
use Illuminate\Support\Facades\Facade;

class GhnApi extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return Ghn::class;
    }
}
