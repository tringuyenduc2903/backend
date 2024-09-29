<?php

namespace App\Api\PayOS;

use PayOS\PayOS;

class PayOSOrderMotorcycle extends PayOS
{
    public function __construct()
    {
        parent::__construct(
            config('services.payos_order_motorcycle.client_id'),
            config('services.payos_order_motorcycle.client_secret'),
            config('services.payos_order_motorcycle.checksum'),
            config('services.payos_order_motorcycle.partner_code')
        );
    }
}
