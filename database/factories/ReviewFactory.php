<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Employee;
use App\Models\Option;
use App\Models\Review;
use Illuminate\Database\Eloquent\Factories\Factory;
use Random\RandomException;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     *
     * @throws RandomException
     */
    public function definition(): array
    {
        return [
            'content' => mb_substr(
                vnfaker()->paragraphs(2, glue: ' '),
                0,
                254
            ),
        ];
    }

    /**
     * Indicate that the model's review.
     */
    public function review(): static
    {
        $images = [];

        for ($i = 0; $i < random_int(0, 3); $i++) {
            $images[] = fake()->image(
                dir: config('filesystems.disks.review.root'),
                fullPath: false,
            );
        }

        return $this->state(fn (array $attributes) => [
            'reviewable_id' => Customer::inRandomOrder()->first()->id,
            'reviewable_type' => Customer::class,
            'parent_id' => Option::inRandomOrder()->first()->id,
            'parent_type' => Option::class,
            'rate' => random_int(1, 5),
            'images' => json_encode($images, JSON_UNESCAPED_UNICODE),
        ]);
    }

    /**
     * Indicate that the model's response.
     */
    public function reply(): static
    {
        return $this->state(fn (array $attributes) => [
            'reviewable_id' => Employee::inRandomOrder()->first()->id,
            'reviewable_type' => Employee::class,
        ]);
    }
}
