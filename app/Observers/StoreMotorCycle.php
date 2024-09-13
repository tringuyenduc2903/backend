<?php

namespace App\Observers;

use App\Enums\MotorCycleStatus;
use App\Models\Employee;
use App\Models\MotorCycle;

class StoreMotorCycle
{
    /**
     * Handle the Vehicle "creating" event.
     */
    public function creating(MotorCycle $motor_cycle): void
    {
        if (backpack_auth()->guest()) {
            return;
        }

        /** @var Employee $employee */
        $employee = backpack_user();

        $motor_cycle->branch_id = $employee->branch_id;
        $motor_cycle->status = MotorCycleStatus::STORAGE;
    }

    /**
     * Handle the Vehicle "updating" event.
     */
    public function updating(MotorCycle $motor_cycle): void
    {
        if (backpack_auth()->guest()) {
            return;
        }

        /** @var Employee $employee */
        $employee = backpack_user();

        $motor_cycle->branch_id = $employee->branch_id;
    }
}
