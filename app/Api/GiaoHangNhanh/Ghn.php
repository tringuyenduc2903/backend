<?php

namespace App\Api\GiaoHangNhanh;

use App\Api\GiaoHangNhanh\V2\ShippingOrder;
use App\Api\GiaoHangNhanh\V2\Shop;
use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Ghn
{
    use MasterData;
    use ShippingOrder;
    use Shop;

    public function __construct(protected PendingRequest $http)
    {
        $url = app()->environment('production')
            ? 'https://online-gateway.ghn.vn/shiip/public-api'
            : 'https://dev-online-gateway.ghn.vn/shiip/public-api';
        $token = config('services.giaohangnhanh.key');

        $this->http = Http::baseUrl($url)
            ->withHeaders([
                'token' => $token,
            ])
            ->accept('application/json');
    }

    protected function handleResponse(Response $response): ?array
    {
        if ($response->failed()) {
            Log::debug($response->json('message'), $response->json() ?? []);

            throw app(Exception::class, [
                'message' => $response->json('message'),
            ]);
        }

        return $response->json('data');
    }
}
