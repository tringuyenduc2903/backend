<?php

namespace App\Actions\GiaoHangNhanh;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class GHNv2
{
    use MasterData;
    use ShippingOrder;

    public function __construct(protected PendingRequest $http)
    {
        $url = app()->environment('product')
            ? 'https://online-gateway.ghn.vn/shiip/public-api/v2'
            : 'https://dev-online-gateway.ghn.vn/shiip/public-api/v2';
        $token = config('services.giaohangnhanh.key');

        $this->http = Http::baseUrl($url)
            ->withHeaders([
                'token' => $token,
            ])
            ->accept('application/json');
    }
}
