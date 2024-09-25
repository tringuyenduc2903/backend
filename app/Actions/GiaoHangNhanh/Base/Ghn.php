<?php

namespace App\Actions\GiaoHangNhanh\Base;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class Ghn
{
    use MasterData;
    use ShippingOrder;
    use Shop;

    public function __construct(protected PendingRequest $http)
    {
        $url = app()->environment('production')
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
