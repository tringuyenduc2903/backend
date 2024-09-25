<?php

namespace App\Actions\GiaoHangNhanh\Cache;

use App\Facades\Ghn;

trait MasterDataCache
{
    private int $address_time = 86400;

    public function province(): array
    {
        return handle_cache(
            fn (): array => Ghn::province(),
            sprintf('%s_%s', __CLASS__, __METHOD__),
            $this->address_time
        );
    }

    public function district(int $province_id): array
    {
        return handle_cache(
            fn (): array => Ghn::district($province_id),
            sprintf('%s_%s_%s', __CLASS__, __METHOD__, $province_id),
            $this->address_time
        );
    }

    public function ward(int $district_id): ?array
    {
        return handle_cache(
            fn (): ?array => Ghn::ward($district_id),
            sprintf('%s_%s_%s', __CLASS__, __METHOD__, $district_id),
            $this->address_time
        );
    }
}
