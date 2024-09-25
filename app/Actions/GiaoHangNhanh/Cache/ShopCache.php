<?php

namespace App\Actions\GiaoHangNhanh\Cache;

use App\Facades\Ghn;

trait ShopCache
{
    private int $shop_time = 3600;

    public function shop(array $data): array
    {
        return handle_cache(
            fn (): array => Ghn::shop($data),
            sprintf('%s_%s_%s', __CLASS__, __METHOD__, json_encode($data)),
            $this->fee_time
        );
    }
}
