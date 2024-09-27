<?php

namespace App\Actions;

use App\Enums\OrderShippingMethod;
use App\Facades\Ghn;
use App\Models\Address;
use App\Models\Option;

class OrderPrice
{
    public function __construct(
        protected array $options,
        protected int   $shipping_method,
        protected int   $address_id,
        protected array $items = [],
        protected float $price = 0,
        protected float $tax = 0,
        protected float $shipping_fee = 0,
        protected float $handling_fee = 0,
        protected float $total = 0,
        protected int   $weight = 0,
        protected int   $length = 0,
        protected int   $width = 0,
        protected int   $height = 0,
    )
    {
    }

    public function getPriceQuote(): array
    {
        $this->handleItems();
        $this->handleShippingFee();
        $this->handleHandlingFee();
        $this->handleTotal();

        return [
            'weight' => $this->weight,
            'options' => $this->options,
            'price' => $this->price,
            'tax' => $this->tax,
            'shipping_fee' => $this->shipping_fee,
            'handling_fee' => $this->handling_fee,
            'total' => $this->total,
        ];
    }

    protected function handleItems(): void
    {
        $this->items = array_map(
            function (array $item) {
                $option = Option::findOrFail($item['option'] ?? $item['option_id']);

                // Handle price
                $this->price += $option->price * $item['amount'];

                // Handle tax
                $this->tax += (($option->price * $option->value_added_tax) / (100 + $option->value_added_tax)) * $item['amount'];

                // Handle weight, length, width, height
                $this->weight += $option->weight * $item['amount'];
                $this->length += $option->length * $item['amount'];
                $this->width += $option->width * $item['amount'];
                $this->height += $option->height * $item['amount'];

                return [
                    'name' => $option->product->name,
                    'code' => $option->sku,
                    'quantity' => (int)$item['amount'],
                    'weight' => (int)$option->weight,
                    'length' => (int)$option->length,
                    'width' => (int)$option->width,
                    'height' => (int)$option->height,
                ];
            },
            $this->options
        );
    }

    protected function handleShippingFee(): void
    {
        if ($this->shipping_method != OrderShippingMethod::DOOR_TO_DOOR_DELIVERY) {
            return;
        }

        $address = Address::findOrFail($this->address_id);

        $data = Ghn::fee([
            'to_district_id' => $address->district->ghn_id,
            'to_ward_code' => $address->ward?->ghn_id,
            'weight' => $this->weight,
            'length' => $this->length,
            'width' => $this->width,
            'height' => $this->height,
            'insurance_value' => (int)$this->price,
            'items' => $this->items,
        ]);

        $this->shipping_fee = $data['total'];
    }

    protected function handleHandlingFee(): void
    {
        $this->handling_fee = 0;
    }

    protected function handleTotal(): void
    {
        $this->total = $this->price + $this->shipping_fee + $this->handling_fee;
    }
}
