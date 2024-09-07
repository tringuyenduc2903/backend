<?php

namespace App\Enums;

use Rexlabs\Enum\Enum;

/**
 * The OptionType enum.
 *
 * @method static self NEW()
 * @method static self USED()
 * @method static self REFURBISHED()
 */
class OptionType extends Enum
{
    const NEW = 0;

    const USED = 1;

    const REFURBISHED = 2;

    /**
     * Retrieve a map of enum keys and values.
     */
    public static function map(): array
    {
        return [
            static::NEW => trans('New product'),
            static::USED => trans('Used product'),
            static::REFURBISHED => trans('Refurbished product'),
        ];
    }
}
