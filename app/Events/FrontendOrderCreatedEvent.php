<?php

namespace App\Events;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Queue\SerializesModels;

class FrontendOrderCreatedEvent
{
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Order $order,
        public Customer $customer,
        public string $cancel_url,
        public string $return_url,
    ) {}
}
