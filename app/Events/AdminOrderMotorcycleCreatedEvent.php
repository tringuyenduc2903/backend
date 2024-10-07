<?php

namespace App\Events;

use App\Models\Employee;
use App\Models\OrderMotorcycle;
use Illuminate\Queue\SerializesModels;

class AdminOrderMotorcycleCreatedEvent
{
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public OrderMotorcycle $order,
        public Employee $employee
    ) {}
}
