<?php

namespace App\Actions;

use App\Actions\GiaoHangNhanh\MasterData;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class GHNv2
{
    use MasterData;

    public function __construct(protected PendingRequest $http)
    {
        $url = app()->environment('product')
            ? 'https://online-gateway.ghn.vn'
            : 'https://dev-online-gateway.ghn.vn';
        $token = config('services.giaohangnhanh.key');

        $this->http = Http::baseUrl($url)
            ->withHeaders([
                'token' => $token,
            ])
            ->accept('application/json');
    }
}
