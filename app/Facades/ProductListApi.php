<?php

namespace App\Facades;

use App\Actions\Product\ProductList;
use Illuminate\Support\Facades\Facade;

class ProductListApi extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return ProductList::class;
    }
}
