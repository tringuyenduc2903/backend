<?php

namespace App\Api\GiaoHangNhanh;

use Illuminate\Http\Client\ConnectionException;

trait MasterData
{
    protected int $address_time = 86400;

    public function provinceCache(): array
    {
        return handle_cache(
            /**
             * @throws ConnectionException
             */
            fn (): array => $this->province(),
            sprintf('%s_%s', __CLASS__, __METHOD__),
            $this->address_time
        );
    }

    /**
     * @throws ConnectionException
     */
    public function province(): array
    {
        return $this->handleResponse(
            $this->http->post('master-data/province')
        );
    }

    public function districtCache(int $province_id): array
    {
        return handle_cache(
            /**
             * @throws ConnectionException
             */
            fn (): array => $this->district($province_id),
            sprintf('%s_%s_%s', __CLASS__, __METHOD__, $province_id),
            $this->address_time
        );
    }

    /**
     * @throws ConnectionException
     */
    public function district(int $province_id): array
    {
        return $this->handleResponse(
            $this->http->post('master-data/district', [
                'province_id' => $province_id,
            ])
        );
    }

    public function wardCache(int $district_id): ?array
    {
        return handle_cache(
            /**
             * @throws ConnectionException
             */
            fn (): ?array => $this->ward($district_id),
            sprintf('%s_%s_%s', __CLASS__, __METHOD__, $district_id),
            $this->address_time
        );
    }

    /**
     * @throws ConnectionException
     */
    public function ward(int $district_id): ?array
    {
        return $this->handleResponse(
            $this->http->post('master-data/ward', [
                'district_id' => $district_id,
            ])
        );
    }
}
