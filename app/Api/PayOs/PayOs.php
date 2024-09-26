<?php

namespace App\Api\PayOs;

use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PayOs
{
    public function __construct(protected PendingRequest $http)
    {
        $this->http = Http::baseUrl('https://api-merchant.payos.vn')
            ->withHeaders([
                'x-client-id' => config('services.payos.client_id'),
                'x-api-key' => config('services.payos.client_secret'),
                'x-partner-code' => config('services.payos.partner_code'),
            ])
            ->accept('application/json');
    }

    /**
     * @throws ConnectionException
     * @throws Exception
     */
    public function createLink(array $data): array
    {
        $data['signature'] = hash_hmac(
            'sha256',
            config('services.payos.checksum'),
            sprintf(
                'amount=%s&cancelUrl=%s&description=%s&orderCode=%s&returnUrl=%s',
                $data['amount'],
                $data['cancelUrl'],
                $data['description'],
                $data['orderCode'],
                $data['returnUrl']
            )
        );

        handle_validate_failure(
            $validator = Validator::make($data, [
                'orderCode' => ['required', 'integer'],
                'amount' => ['required', 'integer'],
                'description' => ['required', 'string', 'max:9'],
                'buyerName' => ['sometimes', 'string'],
                'buyerEmail' => ['sometimes', 'string', 'email:rfc,dns'],
                'buyerPhone' => ['sometimes', 'string', 'phone:VN'],
                'buyerAddress' => ['sometimes', 'string'],
                'items' => ['sometimes', 'array'],
                'items.*.name' => ['required', 'string'],
                'items.*.quantity' => ['required', 'integer'],
                'items.*.price' => ['required', 'integer'],
                'cancelUrl' => ['required', 'string'],
                'returnUrl' => ['required', 'string'],
                'expiredAt' => ['sometimes', 'integer'],
                'signature' => ['required', 'string'],
            ])
        );

        return $this->handleResponse(
            $this->http->post('v2/payment-requests', $validator->validated()),
            $data['signature']
        );
    }

    protected function handleResponse(Response $response, string $signature): ?array
    {
        if ($response->failed()) {
            Log::debug($response->json('message'), $response->json() ?? []);

            throw app(Exception::class, [
                'message' => $response->json('message'),
            ]);
        }

        if ($signature !== $response->json('signature')) {
            throw app(Exception::class, [
                'message' => 'Data is illegally changed during the process of sending and receiving data',
            ]);
        }

        return $response->json('data');
    }
}
