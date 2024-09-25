<?php

namespace App\Actions;

use App\Enums\OrderShippingType;
use App\Facades\GHNv2Cache;
use App\Models\Address;
use App\Models\Option;

class OrderPrice
{
    public function __construct(
        protected array $options,
        protected int $shipping_type,
        protected int $address_id,
        protected float $price = 0,
        protected float $tax = 0,
        protected float $shipping_fee = 0,
        protected float $handling_fee = 0,
        protected float $total = 0,
        protected int $weight = 0,
    ) {}

    public function getPriceQuote(): array
    {
        $this->handleOptions();

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

    protected function handleOptions(): void
    {
        array_map(
            function (array $item) {
                $option = Option::findOrFail($item['option'] ?? $item['option_id']);
                $amount = (int) $item['amount'];

                // Handle price
                $this->price += $option->price * $amount;

                // Handle tax
                $this->tax += (($option->price * $option->value_added_tax) / (100 + $option->value_added_tax)) * $amount;

                // Handle weight
                $this->weight += $option->weight * $amount;

                return [
                    'option' => $option->id,
                    'price' => $option->price,
                    'amount' => $amount,
                    'value_added_tax' => $option->value_added_tax,
                ];
            },
            $this->options
        );
    }

    protected function handleShippingFee(): void
    {
        if ($this->shipping_type !== OrderShippingType::DOOR_TO_DOOR_DELIVERY) {
            return;
        }

        $address = Address::findOrFail($this->address_id);

        $data = GHNv2Cache::fee([
            'to_district_id' => $address->district->ghn_id,
            'to_ward_code' => $address->ward?->ghn_id,
            'weight' => $this->weight,
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
