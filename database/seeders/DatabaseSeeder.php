<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Customer::factory(10)->create();

        Customer::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
