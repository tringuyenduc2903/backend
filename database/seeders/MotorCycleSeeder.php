<?php

namespace Database\Seeders;

use App\Models\MotorCycle;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MotorCycleSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        MotorCycle::factory(1000)->create();
    }
}
