<?php

namespace App\Events;

use App\Models\Employee;
use App\Models\Order;
use Illuminate\Queue\SerializesModels;

class OrderCreatedEvent
{
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Order $order,
        public Employee $employee
    ) {}
}
