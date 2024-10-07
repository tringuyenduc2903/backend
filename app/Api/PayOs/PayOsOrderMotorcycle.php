<?php

namespace App\Api\PayOs;

use App\Models\OrderMotorcycle;
use Exception;
use Illuminate\Validation\ValidationException;
use PayOS\PayOS;

class PayOsOrderMotorcycle
{
    protected PayOS $payOS;

    public function __construct()
    {
        $this->payOS = app(PayOS::class, [
            'clientId' => config('services.payos_order_motorcycle.client_id'),
            'apiKey' => config('services.payos_order_motorcycle.client_secret'),
            'checksumKey' => config('services.payos_order_motorcycle.checksum'),
            'partnerCode' => config('services.payos_order_motorcycle.partner_code'),
        ]);
    }

    /**
     * @throws Exception
     */
    public function verifyPaymentWebhookData(array $webhookBody): array
    {
        return $this->payOS->verifyPaymentWebhookData($webhookBody);
    }

    /**
     * @throws Exception
     */
    public function getPaymentLinkInformation(OrderMotorcycle $orderCode): array
    {
        return $this->payOS->getPaymentLinkInformation($orderCode->id);
    }

    /**
     * @throws Exception
     */
    public function cancelPaymentLink(OrderMotorcycle $orderCode, ?string $cancellationReason = null): array
    {
        return $this->payOS->cancelPaymentLink($orderCode->id, $cancellationReason);
    }

    public function createPaymentLink(
        OrderMotorcycle $paymentData,
        ?string $cancel_url = null,
        ?string $return_url = null,
    ): array {
        try {
            return $this->payOS->createPaymentLink([
                'orderCode' => $paymentData->id,
                'amount' => (int) $paymentData->total,
                'description' => sprintf('%s: %s', trans('Order'), $paymentData->id),
                'buyerName' => $paymentData->address->customer_name,
                'buyerEmail' => $paymentData->customer->email,
                'buyerPhone' => $paymentData->address->customer_phone_number,
                'buyerAddress' => $paymentData->address->address_preview,
                'items' => [[
                    'name' => $paymentData->option->product->name,
                    'quantity' => $paymentData->amount,
                    'price' => (int) $paymentData->price,
                ]],
                'cancelUrl' => $cancel_url ?: route('transaction-motorcycles.show', ['id' => $paymentData->id]),
                'returnUrl' => $return_url ?: route('order-motorcycles.show', ['id' => $paymentData->id]),
            ]);
        } catch (Exception) {
            throw ValidationException::withMessages([
                'payment_method' => trans(':method_name :method_value is not available for this order', [
                    'method_name' => trans('Payment method'),
                    'method_value' => trans('Bank transfer'),
                ]),
            ]);
        }
    }
}
