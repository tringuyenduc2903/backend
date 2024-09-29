<?php

namespace App\Enums;

enum ProductTypeEnum: string
{
    case MOTOR_CYCLE = 'motor-cycle';

    case SQUARE_PARTS = 'square-parts';

    case ACCESSORIES = 'accessories';

    public static function valueForKey(int $key): ?string
    {
        return match ($key) {
            ProductType::MOTOR_CYCLE => self::MOTOR_CYCLE->value,
            ProductType::SQUARE_PARTS => self::SQUARE_PARTS->value,
            ProductType::ACCESSORIES => self::ACCESSORIES->value,
            default => null,
        };
    }

    public function key(): int
    {
        return match ($this) {
            self::MOTOR_CYCLE => ProductType::MOTOR_CYCLE,
            self::SQUARE_PARTS => ProductType::SQUARE_PARTS,
            self::ACCESSORIES => ProductType::ACCESSORIES,
        };
    }
}
