<?php

namespace App\Enums;

use Rexlabs\Enum\Enum;

/**
 * The CustomerProvider enum.
 *
 * @method static self GOOGLE()
 * @method static self FACEBOOK()
 */
class CustomerProvider extends Enum
{
    const GOOGLE = 0;

    const FACEBOOK = 1;

    /**
     * Retrieve a map of enum keys and values.
     */
    public static function map(): array
    {
        return [
            static::GOOGLE => CustomerProviderEnum::GOOGLE->value,
            static::FACEBOOK => CustomerProviderEnum::FACEBOOK->value,
        ];
    }
}
