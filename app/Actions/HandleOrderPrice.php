<?php

namespace App\Actions;

use App\Enums\OrderShippingType;
use App\Models\Address;
use App\Models\Option;

class HandleOrderPrice
{
    public function __construct(
        protected ?array $options = null,
        protected ?int $shipping_type = null,
        protected ?int $address = null,
    ) {
        if (! $options) {
            $this->options = request('options');
        }

        if (! $shipping_type) {
            $this->shipping_type = request('shipping_type');
        }

        if (! $address) {
            $this->address = request('address');
        }
    }

    public function getPriceQuote(): array
    {
        $price = 0;
        $tax = 0;
        $weight = 0;
        $name = null;
        $quantity = 0;

        $options = $this->handleOptions(
            $price,
            $tax,
            $weight,
            $quantity,
            $name
        );

        $shipping_fee = 0;

        $this->handleShippingFee(
            $weight,
            $name,
            $quantity,
            $shipping_fee
        );

        $handling_fee = 0;

        $total = $price +
            $shipping_fee +
            $handling_fee;

        return [
            'weight' => $weight,
            'quantity' => $quantity,
            'name' => $name,
            'options' => $options,
            'price' => $price,
            'tax' => $tax,
            'shipping_fee' => $shipping_fee,
            'handling_fee' => $handling_fee,
            'total' => $total,
        ];
    }

    protected function handleOptions(
        float &$price,
        float &$tax,
        int &$weight,
        int &$quantity,
        ?string &$name = null
    ): array {
        return array_map(
            function (array $invoice_product) use (
                &$price,
                &$tax,
                &$weight,
                &$quantity,
                &$name
            ) {
                // Find option
                $option = Option::findOrFail($invoice_product['option']);

                // Handle price
                $amount = (int) $invoice_product['amount'];
                $price += $option->price * $amount;

                // Handle tax
                $handle_tax_1 = $option->price * $option->value_added_tax;
                $handle_tax_2 = 100 + $option->value_added_tax;
                $handle_tax_3 = $handle_tax_1 / $handle_tax_2;
                $tax += $handle_tax_3 * $amount;

                // Handle weight
                $weight += $option->weight * $amount;

                // Handle name
                if (! $name) {
                    $name = $option->product->name;
                }

                // Handle quantity
                $quantity += $amount;

                return [
                    'id' => null,
                    'option' => $option->id,
                    'price' => $option->price,
                    'amount' => $amount,
                    'value_added_tax' => $option->value_added_tax,
                ];
            },
            array_values($this->options)
        );
    }

    protected function handleShippingFee(
        int $weight,
        string $name,
        int $quantity,
        float &$shipping_fee
    ): void {
        if ($this->shipping_type !== OrderShippingType::DOOR_TO_DOOR_DELIVERY) {
            return;
        }

        $address = Address::findOrFail($this->address);

        $data = app(GHNv2::class)->fee(
            $address->district->ghn_id,
            $address->ward?->ghn_id,
            $weight,
            $name,
            $quantity
        );

        $shipping_fee = $data['total'];
    }
}
