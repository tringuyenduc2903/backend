<?php

namespace Database\Factories;

use App\Enums\CustomerIdentification;
use App\Models\Identification;
use App\Models\Province;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Random\RandomException;

/**
 * @extends Factory<Identification>
 */
class IdentificationFactory extends Factory
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
        $type = fake()->randomElement(
            CustomerIdentification::keys()
        );
        $num_9_digits = fake()->randomNumber(9);
        $num_12_digits = random_int(100000000000, 999999999999);

        return [
            'type' => $type,
            'number' => match ($type) {
                CustomerIdentification::IDENTITY_CARD => fake()->randomElement([$num_9_digits, $num_12_digits]),
                CustomerIdentification::CITIZEN_IDENTIFICATION_CARD => $num_12_digits,
                CustomerIdentification::PASSPORT => strtoupper(Str::random(15)),
                default => null,
            },
            'issued_name' => match ($type) {
                CustomerIdentification::IDENTITY_CARD => sprintf(
                    'CA %s',
                    Province::inRandomOrder()->first()->name
                ),
                CustomerIdentification::CITIZEN_IDENTIFICATION_CARD => fake()->randomElement([
                    'Cục Cảnh sát quản lý hành chính về trật tự xã hội',
                    'Cục Cảnh sát đăng ký quản lý cư trú và dữ liệu Quốc gia về dân cư',
                ]),
                CustomerIdentification::PASSPORT => 'Lãnh sự quán Việt Nam',
                default => null,
            },
            'issuance_date' => fake()->dateTimeBetween(),
            'expiry_date' => fake()->dateTimeBetween('now', '+10 year'),
            'default' => fake()->boolean(),
        ];
    }
}
