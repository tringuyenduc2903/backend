<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Customer;
use App\Models\Identification;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Customer::factory(50)
            ->has(Address::factory(5))
            ->has(Identification::factory(5))
            ->create();

        Customer::factory(50)->unverified()
            ->has(Address::factory(5))
            ->has(Identification::factory(5))
            ->create();
    }
}
