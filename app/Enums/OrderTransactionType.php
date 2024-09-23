<?php

namespace App\Enums;

use Rexlabs\Enum\Enum;

/**
 * The OrderTransactionType enum.
 *
 * @method static self PAYMENT_ON_DELIVERY()
 */
class OrderTransactionType extends Enum
{
    const PAYMENT_ON_DELIVERY = 0;

    /**
     * Retrieve a map of enum keys and values.
     */
    public static function map(): array
    {
        return [
            static::PAYMENT_ON_DELIVERY => trans('Payment on delivery'),
        ];
    }
}
