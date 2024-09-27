<?php

namespace App\Api\PayOS;

use Exception;

class PayOS
{
    protected \PayOS\PayOS $pay_os;

    public function __construct()
    {
        $this->pay_os = app(\PayOS\PayOS::class, [
            'clientId' => config('services.payos.client_id'),
            'apiKey' => config('services.payos.client_secret'),
            'checksumKey' => config('services.payos.checksum'),
            'partnerCode' => config('services.payos.partner_code'),
        ]);
    }

    /**
     * @throws Exception
     */
    public function verifyPaymentWebhookData(array $data): array
    {
        return $this->pay_os->verifyPaymentWebhookData($data);
    }

    /**
     * @throws Exception
     */
    public function createPaymentLink(array $data): array
    {
        return $this->pay_os->createPaymentLink($data);
    }

    /**
     * @throws Exception
     */
    public function getPaymentLinkInformation(int $order_id): array
    {
        return $this->pay_os->getPaymentLinkInformation($order_id);
    }
}
