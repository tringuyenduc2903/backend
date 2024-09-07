<?php

namespace App\Enums;

use Rexlabs\Enum\Enum;

/**
 * The OptionStatus enum.
 *
 * @method static self IN_STOCK()
 * @method static self OUT_OF_STOCK()
 */
class OptionStatus extends Enum
{
    const IN_STOCK = 0;

    const OUT_OF_STOCK = 1;

    /**
     * Retrieve a map of enum keys and values.
     */
    public static function map(): array
    {
        return [
            static::IN_STOCK => trans('In stock'),
            static::OUT_OF_STOCK => trans('Out of stock'),
        ];
    }
}
