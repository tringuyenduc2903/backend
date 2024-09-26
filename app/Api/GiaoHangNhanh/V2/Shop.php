<?php

namespace App\Api\GiaoHangNhanh\V2;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Validator;

trait Shop
{
    protected int $shop_time = 3600;

    public function shopCache(array $data): array
    {
        return handle_cache(
            /**
             * @throws ConnectionException
             */
            fn (): array => $this->shop($data),
            sprintf('%s_%s_%s', __CLASS__, __METHOD__, json_encode($data)),
            $this->shop_time
        );
    }

    /**
     * @throws ConnectionException
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

        return $this->handleResponse(
            $this->http->post('v2/shop/all', $validator->validated())
        );
    }
}
