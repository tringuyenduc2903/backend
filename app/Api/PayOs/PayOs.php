<?php

namespace App\Api\PayOs;

use App\Enums\PayOsOrderTypeEnum;
use App\Models\Order;
use App\Models\OrderMotorcycle;
use App\Models\OrderProduct;
use Exception;
use Illuminate\Validation\ValidationException;

class PayOs
{
    public const ORDER = 0;

    public const ORDER_MOTORCYCLE = 1;

    /**
     * @throws Exception
     */
    public function verifyPaymentWebhookData(PayOsOrderTypeEnum $order_type, array $webhookBody): array
    {
        return $this
            ->getConfig($order_type->key())
            ->verifyPaymentWebhookData($webhookBody);
    }

    protected function getConfig(int $mode): \PayOS\PayOS
    {
        return app(\PayOS\PayOS::class, match ($mode) {
            self::ORDER => [
                'clientId' => config('services.payos_order.client_id'),
                'apiKey' => config('services.payos_order.client_secret'),
                'checksumKey' => config('services.payos_order.checksum'),
                'partnerCode' => config('services.payos_order.partner_code'),
            ],
            self::ORDER_MOTORCYCLE => [
                'clientId' => config('services.payos_order_motorcycle.client_id'),
                'apiKey' => config('services.payos_order_motorcycle.client_secret'),
                'checksumKey' => config('services.payos_order_motorcycle.checksum'),
                'partnerCode' => config('services.payos_order_motorcycle.partner_code'),
            ]
        });
    }

    /**
     * @throws Exception
     */
    public function getPaymentLinkInformation(Order|OrderMotorcycle $orderCode): array
    {
        return $this
            ->getConfig($orderCode instanceof Order ? self::ORDER : self::ORDER_MOTORCYCLE)
            ->getPaymentLinkInformation($orderCode->id);
    }

    /**
     * @throws Exception
     */
    public function cancelPaymentLink(
        Order|OrderMotorcycle $orderCode,
        ?string $cancellationReason = null
    ): array {
        return $this
            ->getConfig($orderCode instanceof Order ? self::ORDER : self::ORDER_MOTORCYCLE)
            ->cancelPaymentLink($orderCode->id, $cancellationReason);
    }

    public function createPaymentLink(
        Order|OrderMotorcycle $paymentData,
        ?string $cancel_url = null,
        ?string $return_url = null,
    ): array {
        if (is_null($cancel_url)) {
            $cancel_url = $paymentData instanceof Order
                ? route('transactions.show', ['id' => $paymentData->id])
                : route('transaction-motorcycles.show', ['id' => $paymentData->id]);
        }

        if (is_null($return_url)) {
            $return_url = $paymentData instanceof Order
                ? route('orders.show', ['id' => $paymentData->id])
                : route('order-motorcycles.show', ['id' => $paymentData->id]);
        }

        try {
            return $this
                ->getConfig($paymentData instanceof Order ? self::ORDER : self::ORDER_MOTORCYCLE)
                ->createPaymentLink([
                    'orderCode' => $paymentData->id,
                    'amount' => (int) $paymentData->total,
                    'description' => sprintf('%s: %s', trans('Order'), $paymentData->id),
                    'buyerName' => $paymentData->address->customer_name,
                    'buyerEmail' => $paymentData->customer->email,
                    'buyerPhone' => $paymentData->address->customer_phone_number,
                    'buyerAddress' => $paymentData->address->address_preview,
                    'items' => $paymentData instanceof Order
                        ? $paymentData->options
                            ->map(fn (OrderProduct $order_product): array => [
                                'name' => $order_product->option->product->name,
                                'quantity' => $order_product->amount,
                                'price' => (int) $order_product->price,
                            ])
                            ->toArray()
                        : [[
                            'name' => $paymentData->option->product->name,
                            'quantity' => $paymentData->amount,
                            'price' => (int) $paymentData->price,
                        ]],
                    'cancelUrl' => $cancel_url,
                    'returnUrl' => $return_url,
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
