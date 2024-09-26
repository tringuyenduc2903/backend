<?php

namespace App\Api\GiaoHangNhanh;

use Exception;
use Illuminate\Http\Client\ConnectionException;

trait MasterData
{
    private int $address_time = 86400;

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
     * @throws Exception
     */
    public function province(): array
    {
        handle_ghn_api(
            $response = $this->http->post('master-data/province')
        );

        return $response->json('data');
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
     * @throws Exception
     */
    public function district(int $province_id): array
    {
        handle_ghn_api(
            $response = $this->http->post('master-data/district', [
                'province_id' => $province_id,
            ])
        );

        return $response->json('data');
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
     * @throws Exception
     */
    public function ward(int $district_id): ?array
    {
        handle_ghn_api(
            $response = $this->http->post('master-data/ward', [
                'district_id' => $district_id,
            ])
        );

        return $response->json('data');
    }
}
