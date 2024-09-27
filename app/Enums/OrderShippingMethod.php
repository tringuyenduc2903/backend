<?php

namespace App\Enums;

use Rexlabs\Enum\Enum;

/**
 * The OrderShippingMethod enum.
 *
 * @method static self PICKUP_AT_STORE()
 * @method static self DOOR_TO_DOOR_DELIVERY()
 */
class OrderShippingMethod extends Enum
{
    const PICKUP_AT_STORE = 0;

    const DOOR_TO_DOOR_DELIVERY = 1;

    /**
     * Retrieve a map of enum keys and values.
     */
    public static function map(): array
    {
        return [
            static::PICKUP_AT_STORE => trans('Pickup at Store'),
            static::DOOR_TO_DOOR_DELIVERY => trans('Door-to-door delivery'),
        ];
    }
}
