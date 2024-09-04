<?php

namespace App\Actions\GiaoHangNhanh;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;

class AddressApi extends Api
{
    protected string $province_path = 'shiip/public-api/master-data/province';

    protected string $district_path = 'shiip/public-api/master-data/district';

    protected string $ward_path = 'shiip/public-api/master-data/ward';

    public function getProvinces(): array
    {
        try {
            return $this->http_client
                ->get($this->province_path)
                ->json('data');
        } catch (ConnectionException $exception) {
            Log::debug($exception->getMessage(), [
                'class' => __CLASS__,
                'function' => __FUNCTION__,
            ]);

            return [];
        }
    }

    public function getDistricts(int $province_id): array
    {
        try {
            return $this->http_client
                ->post(
                    $this->district_path, [
                        'province_id' => $province_id,
                    ])
                ->json('data');
        } catch (ConnectionException $exception) {
            Log::debug($exception->getMessage(), [
                'class' => __CLASS__,
                'function' => __FUNCTION__,
            ]);

            return [];
        }
    }

    public function getWards(int $district_id): ?array
    {
        try {
            return $this->http_client
                ->post(
                    $this->ward_path, [
                        'district_id' => $district_id,
                    ])
                ->json('data');
        } catch (ConnectionException $exception) {
            Log::debug($exception->getMessage(), [
                'class' => __CLASS__,
                'function' => __FUNCTION__,
            ]);

            return null;
        }
    }
}
