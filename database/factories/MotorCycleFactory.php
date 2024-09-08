<?php

namespace Database\Factories;

use App\Enums\MotorCycleStatus;
use App\Enums\ProductType;
use App\Models\Branch;
use App\Models\MotorCycle;
use App\Models\Option;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MotorCycle>
 */
class MotorCycleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'chassis_number' => fake()->unique()->randomNumber(9),
            'engine_number' => fake()->unique()->randomNumber(7),
            'status' => fake()->randomElement(MotorCycleStatus::keys()),
            'option_id' => Option::whereHas(
                'product',
                function (Builder $query) {
                    /** @var Product $query */
                    return $query->whereType(ProductType::MOTOR_CYCLE);
                }
            )->inRandomOrder()->first()->id,
            'branch_id' => Branch::inRandomOrder()->first()->id,
        ];
    }
}
