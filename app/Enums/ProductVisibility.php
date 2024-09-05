<?php

namespace App\Enums;

use Rexlabs\Enum\Enum;

/**
 * The ProductVisibility enum.
 *
 * @method static self CATALOG()
 * @method static self SEARCH()
 * @method static self CATALOG_AND_SEARCH()
 * @method static self NOT_VISIBLE_INDIVIDUALLY()
 */
class ProductVisibility extends Enum
{
    const NOT_VISIBLE_INDIVIDUALLY = 0;

    const CATALOG = 1;

    const SEARCH = 2;

    const CATALOG_AND_SEARCH = 3;

    /**
     * Retrieve a map of enum keys and values.
     */
    public static function map(): array
    {
        return [
            static::NOT_VISIBLE_INDIVIDUALLY => trans('Not Visible Individually'),
            static::CATALOG => trans('Catalog'),
            static::SEARCH => trans('Search'),
            static::CATALOG_AND_SEARCH => trans('Catalog and Search'),
        ];
    }
}
