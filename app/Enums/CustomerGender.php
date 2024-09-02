<?php

namespace App\Enums;

use Rexlabs\Enum\Enum;

/**
 * The CustomerGender enum.
 *
 * @method static self MALE()
 * @method static self FEMALE()
 * @method static self NA()
 */
class CustomerGender extends Enum
{
    const MALE = 0;

    const FEMALE = 1;

    const NA = 2;

    /**
     * Retrieve a map of enum keys and values.
     */
    public static function map(): array
    {
        return [
            static::MALE => trans('Male'),
            static::FEMALE => trans('Female'),
            static::NA => trans('Not Specified'),
        ];
    }
}
