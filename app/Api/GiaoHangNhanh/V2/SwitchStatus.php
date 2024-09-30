<?php

namespace App\Api\GiaoHangNhanh\V2;

use App\Models\Order;
use Illuminate\Http\Client\ConnectionException;

trait SwitchStatus
{
    /**
     * @throws ConnectionException
     */
    public function cancelOrder(Order $order): array
    {
        return $this->http
            ->post('v2/switch-status/cancel', [
                'shop_id' => current_store(),
                'order_codes' => [$order->id],
            ])
            ->json('data');
    }
}
