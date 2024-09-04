<?php

namespace App\Actions\GiaoHangNhanh;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class Api
{
    public function __construct(protected PendingRequest $http_client)
    {
        $this->http_client = Http::baseUrl(app()->environment('product')
            ? 'https://online-gateway.ghn.vn'
            : 'https://dev-online-gateway.ghn.vn')
            ->withHeaders([
                'token' => config('services.giaohangnhanh.key'),
            ])
            ->accept('application/json');
    }
}
