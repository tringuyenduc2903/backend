<?php

namespace App\Actions\GiaoHangNhanh;

class StoreCache
{
    protected int $cache_time;

    public function __construct(protected StoreApi $store_api)
    {
        $this->cache_time = config('services.giaohangnhanh.cache_time.store');
    }

    public function stores(int $limit = 200, ?int $offset = null, ?string $client_phone = null): array
    {
        return handle_cache(
            fn () => $this->store_api->stores($limit, $offset, $client_phone),
            sprintf('%s_%s', __CLASS__, __METHOD__),
            $this->cache_time
        );
    }
}
