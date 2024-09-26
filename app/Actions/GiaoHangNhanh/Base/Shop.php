<?php

namespace App\Actions\GiaoHangNhanh\Base;

use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Validator;

trait Shop
{
    /**
     * @throws ConnectionException
     * @throws Exception
     */
    public function shop(array $data): array
    {
        handle_validate_failure(
            $validator = Validator::make($data, [
                'offset' => ['sometimes', 'integer', 'min:0'],
                'limit' => ['sometimes', 'integer', 'min:0'],
                'clientphone' => ['sometimes', 'string'],
            ])
        );

        handle_ghn_api(
            $response = $this->http->post('v2/shop/all', $validator->validated())
        );

        return $response->json('data');
    }
}
