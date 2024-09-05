<?php

namespace App\Actions\GiaoHangNhanh;

use Illuminate\Http\Client\ConnectionException;

class StoreApi extends Api
{
    protected string $store_path = 'shiip/public-api/v2/shop/all';

    public function stores(int $limit = 200, ?int $offset = null, ?string $client_phone = null): array
    {
        try {
            $data = [
                'limit' => $limit,
            ];

            if ($offset) {
                $data['offset'] = $offset;
            }

            if ($client_phone) {
                $data['clientphone'] = $client_phone;
            }

            $response = $this->http_client->post($this->store_path, $data ?? []);

            handle_api_call_failure($response, __CLASS__, __FUNCTION__);

            return $response->json('data');
        } catch (ConnectionException $exception) {
            handle_exception($exception, __CLASS__, __FUNCTION__);

            return [];
        }
    }
}
