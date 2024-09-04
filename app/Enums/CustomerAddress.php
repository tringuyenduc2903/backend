<?php

namespace App\Enums;

use Rexlabs\Enum\Enum;

/**
 * The CustomerAddress enum.
 *
 * @method static self HOME()
 * @method static self COMPANY()
 */
class CustomerAddress extends Enum
{
    const HOME = 0;

    const COMPANY = 1;

    /**
     * Retrieve a map of enum keys and values.
     */
    public static function map(): array
    {
        return [
            static::HOME => trans('Home'),
            static::COMPANY => trans('Company'),
        ];
    }
}
