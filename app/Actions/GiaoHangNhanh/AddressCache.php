<?php

namespace App\Actions\GiaoHangNhanh;

use App\Actions\HandleCache;

class AddressCache extends HandleCache
{
    protected int $cache_time;

    public function __construct(protected AddressApi $address_api)
    {
        $this->cache_time = config('services.giaohangnhanh.cache_time.address');
    }

    public function getProvinces(): array
    {
        return $this->handle(
            fn () => $this->address_api->getProvinces(),
            sprintf('%s_%s', __CLASS__, __METHOD__),
            $this->cache_time
        );
    }

    public function getDistricts(int $province_id): array
    {
        return $this->handle(
            fn () => $this->address_api->getDistricts($province_id),
            sprintf('%s_%s_%s', __CLASS__, __METHOD__, $province_id),
            $this->cache_time
        );
    }

    public function getWards(int $district_id): ?array
    {
        return $this->handle(
            fn () => $this->address_api->getWards($district_id),
            sprintf('%s_%s_%s', __CLASS__, __METHOD__, $district_id),
            $this->cache_time
        );
    }
}
