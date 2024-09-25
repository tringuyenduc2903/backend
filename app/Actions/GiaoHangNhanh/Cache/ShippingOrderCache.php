<?php

namespace App\Actions\GiaoHangNhanh\Cache;

use App\Facades\Ghn;

trait ShippingOrderCache
{
    private int $fee_time = 300;

    private int $available_services_time = 900;

    public function fee(array $data): array
    {
        return handle_cache(
            fn (): array => Ghn::fee($data),
            sprintf('%s_%s_%s', __CLASS__, __METHOD__, json_encode($data)),
            $this->fee_time
        );
    }

    public function availableServices(array $data): array
    {
        return handle_cache(
            fn (): array => Ghn::availableServices($data),
            sprintf('%s_%s_%s', __CLASS__, __METHOD__, json_encode($data)),
            $this->available_services_time
        );
    }
}
