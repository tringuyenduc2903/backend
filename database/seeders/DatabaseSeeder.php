<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ProvinceSeeder::class,
            DistrictSeeder::class,
            WardSeeder::class,
            BranchSeeder::class,
            AdminSeeder::class,
            EmployeeSeeder::class,
            CustomerSeeder::class,
        ]);
    }
}
