<?php

namespace App\Actions\GiaoHangNhanh;

class ShopCache
{
    protected int $cache_time;

    public function __construct(protected ShopApi $store_api)
    {
        $this->cache_time = config('services.giaohangnhanh.cache_time.store');
    }

    public function shops(int $limit = 200, ?int $offset = null, ?string $client_phone = null): array
    {
        return handle_cache(
            fn () => $this->store_api->shops($limit, $offset, $client_phone),
            sprintf('%s_%s', __CLASS__, __METHOD__),
            $this->cache_time
        );
    }
}
