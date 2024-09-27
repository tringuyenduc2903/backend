<?php

namespace App\Enums;

use Rexlabs\Enum\Enum;

/**
 * The OrderPaymentMethod enum.
 *
 * @method static self PAYMENT_ON_DELIVERY()
 * @method static self BANK_TRANSFER()
 */
class OrderPaymentMethod extends Enum
{
    const PAYMENT_ON_DELIVERY = 0;

    const BANK_TRANSFER = 1;

    /**
     * Retrieve a map of enum keys and values.
     */
    public static function map(): array
    {
        return [
            static::PAYMENT_ON_DELIVERY => trans('Payment on delivery'),
            static::BANK_TRANSFER => trans('Bank transfer'),
        ];
    }
}
