<?php

namespace App\Enums;

use Rexlabs\Enum\Enum;

/**
 * The OrderTransactionStatus enum.
 *
 * @method static self PENDING()
 * @method static self PAID()
 * @method static self FAILED()
 */
class OrderTransactionStatus extends Enum
{
    const PENDING = 0;

    const PAID = 1;

    const FAILED = 2;

    /**
     * Retrieve a map of enum keys and values.
     */
    public static function map(): array
    {
        return [
            static::PENDING => trans('Pending'),
            static::PAID => trans('Paid'),
            static::FAILED => trans('Failed'),
        ];
    }
}
