<?php

namespace App\Enums;

use Rexlabs\Enum\Enum;

/**
 * The MotorCycleStatus enum.
 *
 * @method static self SOLD()
 * @method static self STORAGE()
 */
class MotorCycleStatus extends Enum
{
    const SOLD = 0;

    const STORAGE = 1;

    /**
     * Retrieve a map of enum keys and values.
     */
    public static function map(): array
    {
        return [
            static::SOLD => trans('Sold'),
            static::STORAGE => trans('Storage'),
        ];
    }
}
