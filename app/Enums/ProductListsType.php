<?php

namespace App\Enums;

use Rexlabs\Enum\Enum;

/**
 * The ProductListsType enum.
 *
 * @method static self UPSELL()
 * @method static self CROSS_SELL()
 * @method static self RELATED_PRODUCTS()
 */
class ProductListsType extends Enum
{
    const UPSELL = 0;

    const CROSS_SELL = 1;

    const RELATED_PRODUCTS = 2;

    /**
     * Retrieve a map of enum keys and values.
     */
    public static function map(): array
    {
        return [
            static::UPSELL => trans('Upsell products'),
            static::CROSS_SELL => trans('Cross-sell products'),
            static::RELATED_PRODUCTS => trans('Related products'),
        ];
    }
}
