<?php

namespace App\Actions;

use App\Enums\OrderShippingMethod;
use App\Facades\GhnApi;
use App\Models\Address;
use App\Models\Option;

class Fee
{
    protected array $items = [];

    protected float $price = 0;

    protected float $tax = 0;

    protected float $shipping_fee = 0;

    protected float $handling_fee = 0;

    protected float $total = 0;

    protected int $weight = 0;

    protected array $options;

    protected int $shipping_method;

    protected int $address_id;

    public function getFee(
        array $options,
        int $shipping_method,
        int $address_id
    ): array {
        $this->options = $options;
        $this->shipping_method = $shipping_method;
        $this->address_id = $address_id;

        $this->handleItems();
        $this->handleShippingFee();
        $this->handleHandlingFee();
        $this->handleTotal();

        return [
            'items' => $this->items,
            'weight' => $this->weight,
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
                $amount = (int) $item['amount'];

                $make_money = $option->price * $amount;

                // Handle price
                $this->price += $make_money;

                // Handle tax
                $this->tax += (($option->price * $option->value_added_tax) / (100 + $option->value_added_tax)) * $amount;

                // Handle weight
                $this->weight += $option->weight * $amount;

                return [
                    'name' => $option->product->name,
                    'code' => $option->sku,
                    'price' => (int) $option->price,
                    'quantity' => $amount,
                    'weight' => (int) $option->weight,
                    'length' => (int) $option->length,
                    'width' => (int) $option->width,
                    'height' => (int) $option->height,
                    'value_added_tax' => $option->value_added_tax,
                    'make_money' => $make_money,
                ];
            },
            $this->options
        );
    }

    protected function handleShippingFee(): void
    {
        if ($this->shipping_method == OrderShippingMethod::DOOR_TO_DOOR_DELIVERY) {
            $address = Address::findOrFail($this->address_id);

            $data = GhnApi::fee(
                $address,
                $this->weight,
                (int) $this->price,
                $this->items
            );

            $this->shipping_fee = $data['total'];
        }
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
