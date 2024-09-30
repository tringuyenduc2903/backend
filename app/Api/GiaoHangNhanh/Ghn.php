<?php

namespace App\Api\GiaoHangNhanh;

use App\Api\GiaoHangNhanh\V2\ShippingOrder;
use App\Api\GiaoHangNhanh\V2\Shop;
use App\Api\GiaoHangNhanh\V2\SwitchStatus;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class Ghn
{
    use MasterData;
    use ShippingOrder;
    use Shop;
    use SwitchStatus;

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
}
