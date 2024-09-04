<?php

namespace Database\Factories;

use App\Enums\CustomerAddress;
use App\Models\Address;
use App\Models\Province;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $province = Province::inRandomOrder()->first();
        $district = $province->districts()->inRandomOrder()->first();
        $ward = $district->wards()->inRandomOrder()->first();

        return [
            'customer_name' => vnfaker()->fullname(),
            'customer_phone_number' => sprintf(
                '+84%s',
                substr(vnfaker()->mobilephone(), 1)
            ),
            'country' => 'Việt Nam',
            'province_id' => $province->id,
            'district_id' => $district->id,
            'ward_id' => $ward?->id,
            'address_detail' => fake()->streetAddress,
            'type' => fake()->randomElement(CustomerAddress::keys()),
            'default' => fake()->boolean(),
        ];
    }
}
