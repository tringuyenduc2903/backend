<?php

namespace App\Enums;

use Rexlabs\Enum\Enum;

/**
 * The ProductType enum.
 *
 * @method static self MOTOR_CYCLE()
 * @method static self SQUARE_PARTS()
 * @method static self ACCESSORIES()
 */
class ProductType extends Enum
{
    const MOTOR_CYCLE = 0;

    const SQUARE_PARTS = 1;

    const ACCESSORIES = 2;

    /**
     * Retrieve a map of enum keys and values.
     */
    public static function map(): array
    {
        return [
            static::MOTOR_CYCLE => trans('Motor cycle'),
            static::SQUARE_PARTS => trans('Square parts'),
            static::ACCESSORIES => trans('Accessories'),
        ];
    }
}
