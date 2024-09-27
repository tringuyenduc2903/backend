<?php

namespace App\Enums;

use Rexlabs\Enum\Enum;

/**
 * The PayOsStatus enum.
 *
 * @method static self PAID()
 * @method static self CANCELLED()
 * @method static self PENDING()
 * @method static self PROCESSING()
 */
class PayOsStatus extends Enum
{
    const PAID = 'PAID';

    const CANCELLED = 'CANCELLED';

    const PENDING = 'PENDING';

    const PROCESSING = 'PROCESSING';

    /**
     * Retrieve a map of enum keys and values.
     */
    public static function map(): array
    {
        return [
            static::PAID => OrderTransactionStatus::SUCCESSFULLY,
            static::CANCELLED => OrderTransactionStatus::FAILED,
            static::PENDING, static::PROCESSING => OrderTransactionStatus::PENDING,
        ];
    }
}
