<?php

namespace App\Enums;

use Rexlabs\Enum\Enum;

/**
 * The CustomerIdentification enum.
 *
 * @method static self IDENTITY_CARD()
 * @method static self CITIZEN_IDENTIFICATION_CARD()
 * @method static self PASSPORT()
 */
class CustomerIdentification extends Enum
{
    const IDENTITY_CARD = 0;

    const CITIZEN_IDENTIFICATION_CARD = 1;

    const PASSPORT = 2;

    /**
     * Retrieve a map of enum keys and values.
     */
    public static function map(): array
    {
        return [
            static::IDENTITY_CARD => trans('Identity card'),
            static::CITIZEN_IDENTIFICATION_CARD => trans('Citizen identification'),
            static::PASSPORT => trans('Passport'),
        ];
    }
}
