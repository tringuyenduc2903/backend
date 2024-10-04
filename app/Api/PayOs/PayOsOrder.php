<?php

namespace App\Api\PayOs;

use App\Models\Order;
use App\Models\OrderProduct;
use Exception;
use Illuminate\Validation\ValidationException;
use PayOS\PayOS;

class PayOsOrder
{
    protected PayOS $payOS;

    public function __construct()
    {
        $this->payOS = app(PayOS::class, [
            'clientId' => config('services.payos_order.client_id'),
            'apiKey' => config('services.payos_order.client_secret'),
            'checksumKey' => config('services.payos_order.checksum'),
            'partnerCode' => config('services.payos_order.partner_code'),
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
    public function getPaymentLinkInformation(Order $orderCode): array
    {
        return $this->payOS->getPaymentLinkInformation($orderCode->id);
    }

    /**
     * @throws Exception
     */
    public function cancelPaymentLink(Order $orderCode, ?string $cancellationReason = null): array
    {
        return $this->payOS->cancelPaymentLink($orderCode->id, $cancellationReason);
    }

    public function createPaymentLink(Order $paymentData): array
    {
        try {
            return $this->payOS->createPaymentLink([
                'orderCode' => $paymentData->id,
                'amount' => (int) $paymentData->total,
                'description' => sprintf('%s: %s', trans('Order'), $paymentData->id),
                'buyerName' => $paymentData->address->customer_name,
                'buyerEmail' => $paymentData->customer->email,
                'buyerPhone' => $paymentData->address->customer_phone_number,
                'buyerAddress' => $paymentData->address->address_preview,
                'items' => $paymentData->options
                    ->map(fn (OrderProduct $order_product): array => [
                        'name' => $order_product->option->product->name,
                        'quantity' => $order_product->amount,
                        'price' => (int) $order_product->price,
                    ])
                    ->toArray(),
                'cancelUrl' => route('orders.show', ['id' => $paymentData->id]),
                'returnUrl' => route('orders.show', ['id' => $paymentData->id]),
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
