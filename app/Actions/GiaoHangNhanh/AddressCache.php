<?php

namespace App\Actions\GiaoHangNhanh;

class AddressCache
{
    protected int $cache_time;

    public function __construct(protected AddressApi $address_api)
    {
        $this->cache_time = config('services.giaohangnhanh.cache_time.address');
    }

    public function getProvinces(): array
    {
        return handle_cache(
            fn () => $this->address_api->getProvinces(),
            sprintf('%s_%s', __CLASS__, __METHOD__),
            $this->cache_time
        );
    }

    public function getDistricts(int $province_id): array
    {
        return handle_cache(
            fn () => $this->address_api->getDistricts($province_id),
            sprintf('%s_%s_%s', __CLASS__, __METHOD__, $province_id),
            $this->cache_time
        );
    }

    public function getWards(int $district_id): ?array
    {
        return handle_cache(
            fn () => $this->address_api->getWards($district_id),
            sprintf('%s_%s_%s', __CLASS__, __METHOD__, $district_id),
            $this->cache_time
        );
    }
}
