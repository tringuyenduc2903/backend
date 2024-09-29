<?php

namespace App\Enums;

use Rexlabs\Enum\Enum;

/**
 * The OrderMotorcycleLicensePlateRegistration enum.
 *
 * @method static self REGION_I()
 * @method static self REGION_II()
 * @method static self REGION_III()
 */
class OrderMotorcycleLicensePlateRegistration extends Enum
{
    const REGION_I = 0;

    const REGION_II = 1;

    const REGION_III = 2;

    /**
     * Retrieve a map of enum keys and values.
     */
    public static function map(): array
    {
        return [
            static::REGION_I => trans('Region I (Hanoi City, Ho Chi Minh City includes all districts directly under the city, regardless of inner city or suburbs)'),
            static::REGION_II => trans('Region II (Cities directly under the Central Government (except Hanoi City and Ho Chi Minh City) includes all districts directly under the city, regardless of inner city or suburbs; cities under the province, Towns include all wards and communes of cities and towns, regardless of whether they are inner-city wards or suburban communes)'),
            static::REGION_III => trans('Region III (Areas other than region I and region II)'),
        ];
    }
}
