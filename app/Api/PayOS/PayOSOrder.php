<?php

namespace App\Api\PayOS;

use PayOS\PayOS;

class PayOSOrder extends PayOS
{
    public function __construct()
    {
        parent::__construct(
            config('services.payos_order.client_id'),
            config('services.payos_order.client_secret'),
            config('services.payos_order.checksum'),
            config('services.payos_order.partner_code')
        );
    }
}
