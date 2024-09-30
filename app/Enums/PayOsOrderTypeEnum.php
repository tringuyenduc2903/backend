<?php

namespace App\Enums;

use App\Events\AdminOrderCreatedEvent;
use App\Facades\PayOSOrder;
use App\Facades\PayOSOrderMotorcycle;
use App\Listeners\CreateOrderGhnShip;
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

    public function getPaymentLinkInformation(int $order_code): array
    {
        return match ($this) {
            self::ORDER => PayOSOrder::getPaymentLinkInformation($order_code),
            self::ORDER_MOTORCYCLE => PayOSOrderMotorcycle::getPaymentLinkInformation($order_code),
        };
    }

    public function getOrder(int $orderCode): Order|OrderMotorcycle
    {
        return match ($this) {
            self::ORDER => Order::findOrFail($orderCode),
            self::ORDER_MOTORCYCLE => OrderMotorcycle::findOrFail($orderCode),
        };
    }

    public function eventPaid(Order|OrderMotorcycle $order): void
    {
        switch ($this) {
            case self::ORDER:
                $order->update([
                    'status' => OrderStatus::TO_SHIP,
                ]);

                app(CreateOrderGhnShip::class, [
                    'event' => app(AdminOrderCreatedEvent::class, [
                        'order' => $order,
                        'employee' => backpack_user(),
                    ]),
                ]);
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
