<?php

namespace App\Events;

use App\Models\Customer;
use App\Models\OrderMotorcycle;
use Illuminate\Queue\SerializesModels;

class FrontendOrderMotorcycleCreatedEvent
{
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public OrderMotorcycle $order,
        public Customer $customer,
    ) {}
}
