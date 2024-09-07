<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Employee;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Employee::factory(100)->create()->each(function ($employee) {
            $employee->branch()
                ->associate(Branch::inRandomOrder()->first())
                ->save();
        });
    }
}
