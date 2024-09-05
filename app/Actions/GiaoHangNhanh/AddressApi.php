<?php

namespace App\Actions\GiaoHangNhanh;

use Illuminate\Http\Client\ConnectionException;

class AddressApi extends Api
{
    protected string $province_path = 'shiip/public-api/master-data/province';

    protected string $district_path = 'shiip/public-api/master-data/district';

    protected string $ward_path = 'shiip/public-api/master-data/ward';

    public function getProvinces(): array
    {
        try {
            $response = $this->http_client->get($this->province_path);

            handle_api_call_failure($response, __CLASS__, __FUNCTION__);

            return $response->json('data');
        } catch (ConnectionException $exception) {
            handle_exception($exception, __CLASS__, __FUNCTION__);

            return [];
        }
    }

    public function getDistricts(int $province_id): array
    {
        try {
            $response = $this->http_client->post(
                $this->district_path, [
                    'province_id' => $province_id,
                ]);

            handle_api_call_failure($response, __CLASS__, __FUNCTION__);

            return $response->json('data');
        } catch (ConnectionException $exception) {
            handle_exception($exception, __CLASS__, __FUNCTION__);

            return [];
        }
    }

    public function getWards(int $district_id): ?array
    {
        try {
            $response = $this->http_client->post(
                $this->ward_path, [
                    'district_id' => $district_id,
                ]);

            handle_api_call_failure($response, __CLASS__, __FUNCTION__);

            return $response->json('data');
        } catch (ConnectionException $exception) {
            handle_exception($exception, __CLASS__, __FUNCTION__);

            return null;
        }
    }
}
