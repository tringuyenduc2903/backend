<?php

namespace App\Actions\GiaoHangNhanh;

use Illuminate\Http\Client\ConnectionException;

class ShopApi extends Api
{
    protected string $shop_path = 'shiip/public-api/v2/shop/all';

    public function shops(int $limit = 200, ?int $offset = null, ?string $client_phone = null): array
    {
        $data = [
            'limit' => $limit,
        ];

        if ($offset) {
            $data['offset'] = $offset;
        }

        if ($client_phone) {
            $data['clientphone'] = $client_phone;
        }

        try {
            $response = $this->http_client->post(
                $this->shop_path,
                $data
            );

            handle_api_call_failure($response, __CLASS__, __FUNCTION__);

            return $response->json('data');
        } catch (ConnectionException $exception) {
            handle_exception($exception, __CLASS__, __FUNCTION__);

            return [];
        }
    }
}
