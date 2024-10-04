<?php

namespace App\Api\GiaoHangNhanh\V2;

use App\Enums\OrderPaymentMethod;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderProduct;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Validation\ValidationException;

trait ShippingOrder
{
    public const HANG_NHE = 2;

    public const HANG_NANG = 5;

    public const NGUOI_BAN_NGUOI_GUI = 1;

    public const NGUOI_MUA_NGUOI_NHAN = 2;

    public const CHO_THU_HANG = 'CHOTHUHANG';

    public const CHO_XEM_HANG_KHONG_THU = 'CHOXEMHANGKHONGTHU';

    public const KHONG_CHO_XEM_HANG = 'KHONGCHOXEMHANG';

    /**
     * @throws ConnectionException
     */
    public function createOrder(Order $order): array
    {
        $data = [
            'shop_id' => current_store(),
            'service_type_id' => $this->getServiceTypeId($order->weight),
            'to_name' => $order->address->customer_name,
            'to_phone' => $order->address->customer_phone_number,
            'to_address' => $order->address->address_detail,
            'to_ward_code' => $order->address->ward->ghn_id,
            'to_district_code' => $order->address->district->ghn_id,
            'weight' => (int) $order->weight,
            'insurance_value' => (int) $order->options()->sum('price'),
            'payment_type_id' => self::NGUOI_BAN_NGUOI_GUI,
            'required_note' => self::CHO_XEM_HANG_KHONG_THU,
            'items' => $order->options
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

        if ($order->payment_method == OrderPaymentMethod::PAYMENT_ON_DELIVERY) {
            $data['cod_amount'] = (int) $order->total;
        }

        if ($order->note) {
            $data['note'] = $order->note;
        }

        $response = $this->http->post('v2/shipping-order/create', $data);

        if ($response->failed()) {
            throw app(Exception::class, [
                'message' => $response->json('message'),
            ]);
        }

        return $response->json('data');
    }

    protected function getServiceTypeId(int $weight): int
    {
        return $weight < 20000 ? self::HANG_NHE : self::HANG_NANG;
    }

    /**
     * @throws ConnectionException
     */
    public function fee(Address $address, int $weight, int $insurance_value, array $items): array
    {
        $response = $this->http
            ->post('v2/shipping-order/fee', [
                'shop_id' => current_store(),
                'service_type_id' => $this->getServiceTypeId($weight),
                'to_district_id' => $address->district->ghn_id,
                'to_ward_code' => $address->ward?->ghn_id,
                'weight' => $weight,
                'insurance_value' => $insurance_value,
                'items' => $items,
            ]);

        if ($response->failed()) {
            throw ValidationException::withMessages([
                'shipping_method' => trans(':method_name :method_value is not available for this order', [
                    'method_name' => trans('Shipping method'),
                    'method_value' => trans('Door-to-door delivery'),
                ]),
            ]);
        }

        return $response->json('data');
    }
}
