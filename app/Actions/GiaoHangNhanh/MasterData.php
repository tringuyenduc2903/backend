<?php

namespace App\Actions\GiaoHangNhanh;

use Exception;
use Illuminate\Http\Client\ConnectionException;

trait MasterData
{
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
