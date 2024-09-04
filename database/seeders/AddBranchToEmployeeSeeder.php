<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Employee;
use Illuminate\Database\Seeder;

class AddBranchToEmployeeSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Employee::each(function (Employee $employee) {
            $employee->branch_id = Branch::inRandomOrder()->first()->id;
            $employee->save();
        });
    }
}
