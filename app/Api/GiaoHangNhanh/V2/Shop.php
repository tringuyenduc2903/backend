<?php

namespace App\Api\GiaoHangNhanh\V2;

use Illuminate\Http\Client\ConnectionException;

trait Shop
{
    protected int $shop_time = 3600;

    public function shop(int $limit = 200): array
    {
        return handle_cache(
            /**
             * @throws ConnectionException
             */
            fn (): array => $this->http
                ->post('v2/shop/all', ['limit' => $limit])
                ->json('data'),
            sprintf('%s_%s_%s', __CLASS__, __METHOD__, $limit),
            $this->shop_time
        );
    }
}
