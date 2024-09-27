<?php

namespace App\Listeners;

use App\Api\GiaoHangNhanh\Ghn;
use App\Enums\OrderPaymentMethod;
use App\Enums\OrderShippingMethod;
use App\Enums\OrderStatus;
use App\Events\OrderCreatedEvent;
use App\Models\OrderProduct;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreateGhnShip implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(OrderCreatedEvent $event): void
    {
        if (
            $event->order->status != OrderStatus::TO_SHIP ||
            $event->order->shipping_method != OrderShippingMethod::DOOR_TO_DOOR_DELIVERY
        ) {
            return;
        }

        $data = [
            'to_name' => $event->order->address->customer_name,
            'to_phone' => $event->order->address->customer_phone_number,
            'to_address' => $event->order->address->address_detail,
            'to_ward_code' => $event->order->address->ward->ghn_id,
            'to_district_code' => $event->order->address->district->ghn_id,
            'weight' => (int) $event->order->options()->withSum('option', 'weight')->value('option_sum_weight'),
            'length' => (int) $event->order->options()->withSum('option', 'length')->value('option_sum_length'),
            'width' => (int) $event->order->options()->withSum('option', 'width')->value('option_sum_width'),
            'height' => (int) $event->order->options()->withSum('option', 'height')->value('option_sum_height'),
            'insurance_value' => (int) $event->order->options()->sum('price'),
            'payment_type_id' => Ghn::NGUOI_BAN_NGUOI_GUI,
            'required_note' => Ghn::CHO_XEM_HANG_KHONG_THU,
            'items' => $event->order->options
                ->map(fn (OrderProduct $order_product): array => [
                    'name' => $order_product->option->product->name,
                    'code' => $order_product->option->sku,
                    'quantity' => $order_product->amount,
                    'price' => (int) $order_product->price,
                    'weight' => $order_product->option->weight,
                    'length' => $order_product->option->length,
                    'width' => $order_product->option->width,
                    'height' => $order_product->option->height,
                    'category' => (object) [
                        'level1' => $order_product->option->product->categories()->first()->name,
                    ],
                ])
                ->toArray(),
        ];

        if ($event->order->payment_method == OrderPaymentMethod::PAYMENT_ON_DELIVERY) {
            $data['cod_amount'] = (int) $event->order->total;
        }

        if ($event->order->note) {
            $data['note'] = $event->order->note;
        }

        $response = \App\Facades\Ghn::createOrder($data);

        $event->order
            ->forceFill([
                'shipping_code' => $response['order_code'],
            ])
            ->save();
    }
}
