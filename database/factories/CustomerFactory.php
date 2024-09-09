<?php

namespace Database\Factories;

use App\Enums\CustomerGender;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected string $password = 'password';

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => vnfaker()->fullname(),
            'email' => vnfaker()->email(),
            'email_verified_at' => now(),
            'phone_number' => sprintf(
                '+84%s',
                substr(vnfaker()->mobilephone(), 1)
            ),
            'phone_number_verified_at' => now(),
            'birthday' => fake()->dateTimeBetween(
                Carbon::now()->subYears(100),
                Carbon::now()->subYears(16)
            ),
            'gender' => fake()->randomElement(CustomerGender::keys()),
            'password' => $this->password,
            'timezone' => fake()->randomElement(
                array_keys(timezone_identifiers_list())
            ),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
            'phone_number_verified_at' => null,
        ]);
    }
}
