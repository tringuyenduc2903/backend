<?php

namespace App\Api\GiaoHangNhanh;

use Illuminate\Http\Client\ConnectionException;

trait MasterData
{
    protected int $address_time = 86400;

    public function province(): array
    {
        return handle_cache(
            /**
             * @throws ConnectionException
             */
            fn (): array => $this->http
                ->post('master-data/province')
                ->json('data'),
            sprintf('%s_%s', __CLASS__, __METHOD__),
            $this->address_time
        );
    }

    public function district(int $province_id): array
    {
        return handle_cache(
            /**
             * @throws ConnectionException
             */
            fn (): array => $this->http
                ->post('master-data/district', [
                    'province_id' => $province_id,
                ])
                ->json('data'),
            sprintf('%s_%s_%s', __CLASS__, __METHOD__, $province_id),
            $this->address_time
        );
    }

    public function ward(int $district_id): ?array
    {
        return handle_cache(
            /**
             * @throws ConnectionException
             */
            fn (): ?array => $this->http
                ->post('master-data/ward', [
                    'district_id' => $district_id,
                ])
                ->json('data'),
            sprintf('%s_%s_%s', __CLASS__, __METHOD__, $district_id),
            $this->address_time
        );
    }
}
