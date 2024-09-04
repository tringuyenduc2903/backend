<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Province;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class BranchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = mb_ucwords(mb_strtolower(vnfaker()->company()));

        $alt = strlen($name) <= 50 ? $name : mb_substr($name, 0, 46).'...';

        $province = Province::inRandomOrder()->first();

        $district = $province->districts()->inRandomOrder()->first();

        $ward = $district->wards()->inRandomOrder()->first();

        return [
            'name' => $name,
            'phone_number' => vnfaker()->mobilephone(),
            'image' => fake()->image(
                dir: config('filesystems.disks.branch.root'),
                fullPath: false,
                word: $name
            ),
            'alt' => $alt,
            'country' => 'Việt Nam',
            'province_id' => $province->id,
            'district_id' => $district->id,
            'ward_id' => $ward?->id,
            'address_detail' => fake()->streetAddress,
        ];
    }
}
