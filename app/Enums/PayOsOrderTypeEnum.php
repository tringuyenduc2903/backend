<?php

namespace App\Enums;

use App\Facades\Ghn;
use App\Facades\PayOSOrder;
use App\Facades\PayOSOrderMotorcycle;
use App\Models\Order;
use App\Models\OrderMotorcycle;

enum PayOsOrderTypeEnum: string
{
    case ORDER = 'order';

    case ORDER_MOTORCYCLE = 'order-motorcycle';

    public function verifyPaymentWebhookData(array $data): array
    {
        return match ($this) {
            self::ORDER => PayOSOrder::verifyPaymentWebhookData($data),
            self::ORDER_MOTORCYCLE => PayOSOrderMotorcycle::verifyPaymentWebhookData($data),
        };
    }

    public function getOrder(int $orderCode): Order|OrderMotorcycle
    {
        return match ($this) {
            self::ORDER => Order::findOrFail($orderCode),
            self::ORDER_MOTORCYCLE => OrderMotorcycle::findOrFail($orderCode),
        };
    }

    public function getPaymentLinkInformation(Order|OrderMotorcycle $order): array
    {
        return match ($this) {
            self::ORDER => PayOSOrder::getPaymentLinkInformation($order),
            self::ORDER_MOTORCYCLE => PayOSOrderMotorcycle::getPaymentLinkInformation($order),
        };
    }

    public function eventPaid(Order|OrderMotorcycle $order): void
    {
        switch ($this) {
            case self::ORDER:
                $order->update([
                    'status' => OrderStatus::TO_SHIP,
                ]);

                if ($order->shipping_method == OrderShippingMethod::DOOR_TO_DOOR_DELIVERY) {
                    Ghn::createOrder($order);
                }
                break;
            case self::ORDER_MOTORCYCLE:
                $order->update([
                    'status' => OrderStatus::TO_RECEIVE,
                ]);
                break;
        }
    }

    public function eventCancelled(Order|OrderMotorcycle $order): void
    {
        $order->update([
            'status' => OrderStatus::CANCELLED,
        ]);
    }
}
