<?php

namespace App\Actions\GiaoHangNhanh;

use App\Facades\GHNv2;

trait ShippingOrderCache
{
    private int $fee_time = 300;

    public function fee(array $data): array
    {
        return handle_cache(
            fn (): array => GHNv2::fee($data),
            sprintf('%s_%s_%s', __CLASS__, __METHOD__, json_encode($data)),
            $this->fee_time
        );
    }
}
