<?php

namespace App\Enums;

use App\Api\PayOs\PayOs;

enum PayOsOrderTypeEnum: string
{
    case ORDER = 'order';

    case ORDER_MOTORCYCLE = 'order-motorcycle';

    public function key(): int
    {
        return match ($this) {
            self::ORDER => PayOs::ORDER,
            self::ORDER_MOTORCYCLE => PayOs::ORDER_MOTORCYCLE,
        };
    }
}
