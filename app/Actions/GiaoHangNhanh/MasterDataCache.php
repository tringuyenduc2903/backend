<?php

namespace App\Actions\GiaoHangNhanh;

use App\Facades\GHNv2;

trait MasterDataCache
{
    private int $address_time = 86400;

    public function province(): array
    {
        return handle_cache(
            fn (): array => GHNv2::province(),
            sprintf('%s_%s', __CLASS__, __METHOD__),
            $this->address_time
        );
    }

    public function district(int $province_id): array
    {
        return handle_cache(
            fn (): array => GHNv2::district($province_id),
            sprintf('%s_%s_%s', __CLASS__, __METHOD__, $province_id),
            $this->address_time
        );
    }

    public function ward(int $district_id): ?array
    {
        return handle_cache(
            fn (): ?array => GHNv2::ward($district_id),
            sprintf('%s_%s_%s', __CLASS__, __METHOD__, $district_id),
            $this->address_time
        );
    }
}
