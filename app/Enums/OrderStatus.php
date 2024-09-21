<?php

namespace App\Enums;

use Rexlabs\Enum\Enum;

/**
 * The OrderStatus enum.
 *
 * @method static self TO_PAY()
 * @method static self TO_SHIP()
 * @method static self TO_RECEIVE()
 * @method static self COMPLETED()
 * @method static self CANCELLED()
 * @method static self RETURN_REFUND()
 */
class OrderStatus extends Enum
{
    const TO_PAY = 0;

    const TO_SHIP = 1;

    const TO_RECEIVE = 2;

    const COMPLETED = 3;

    const CANCELLED = 4;

    const RETURN_REFUND = 5;

    /**
     * Retrieve a map of enum keys and values.
     */
    public static function map(): array
    {
        return [
            static::TO_PAY => trans('To pay'),
            static::TO_SHIP => trans('To ship'),
            static::TO_RECEIVE => trans('To receive'),
            static::COMPLETED => trans('Completed'),
            static::CANCELLED => trans('Cancelled'),
            static::RETURN_REFUND => trans('Return/Refund'),
        ];
    }
}
