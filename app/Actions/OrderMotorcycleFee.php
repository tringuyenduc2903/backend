<?php

namespace App\Actions;

use App\Enums\OrderMotorcycleLicensePlateRegistration;
use App\Enums\OrderMotorcycleRegistration;
use App\Models\Option;

class OrderMotorcycleFee
{
    public array $result;

    protected array $item = [];

    protected float $price = 0;

    protected float $motorcycle_registration_support_fee = 0;

    protected float $registration_fee = 0;

    protected float $license_plate_registration_fee = 0;

    protected float $tax = 0;

    protected float $handling_fee = 0;

    protected float $total = 0;

    public function __construct(
        protected int $option,
        protected bool $motorcycle_registration_support,
        protected ?int $registration_option,
        protected ?int $license_plate_registration_option,
    ) {
        $this->handleItem();
        $this->handleMotorcycleRegistrationSupportFee();
        $this->handleHandlingFee();
        $this->handleTotal();

        $this->result = [
            'item' => $this->item,
            'price' => $this->price,
            'motorcycle_registration_support_fee' => $this->motorcycle_registration_support_fee,
            'registration_fee' => $this->registration_fee,
            'license_plate_registration_fee' => $this->license_plate_registration_fee,
            'tax' => $this->tax,
            'handling_fee' => $this->handling_fee,
            'total' => $this->total,
        ];
    }

    protected function handleItem(): void
    {
        $option = Option::findOrFail($this->option);
        $amount = 1;

        // Handle price
        $this->price += $option->price * $amount;

        // Handle tax
        $this->tax += (($option->price * $option->value_added_tax) / (100 + $option->value_added_tax)) * $amount;

        $this->item = [
            'name' => $option->product->name,
            'code' => $option->sku,
            'price' => (int) $option->price,
            'quantity' => $amount,
        ];
    }

    protected function handleMotorcycleRegistrationSupportFee(): void
    {
        if ($this->motorcycle_registration_support) {
            $this->motorcycle_registration_support_fee = 400000;
            $this->handleRegistrationFee();
            $this->handleLicensePlateRegistrationFee();
        }
    }

    protected function handleRegistrationFee(): void
    {
        $this->registration_fee = $this->price * match ($this->registration_option) {
            OrderMotorcycleRegistration::FIRST_TIME => 5 / 100,
            OrderMotorcycleRegistration::TWO_TIME_ONWARDS => 1 / 100,
            default => null,
        };
    }

    protected function handleLicensePlateRegistrationFee(): void
    {
        if ($this->price < 15000000) {
            $this->license_plate_registration_fee = match ($this->license_plate_registration_option) {
                OrderMotorcycleLicensePlateRegistration::REGION_I,
                OrderMotorcycleLicensePlateRegistration::REGION_II,
                OrderMotorcycleLicensePlateRegistration::REGION_III => 150000,
                default => null,
            };
        } elseif ($this->price < 40000000) {
            $this->license_plate_registration_fee = match ($this->license_plate_registration_option) {
                OrderMotorcycleLicensePlateRegistration::REGION_I => 800000,
                OrderMotorcycleLicensePlateRegistration::REGION_II => 400000,
                OrderMotorcycleLicensePlateRegistration::REGION_III => 200000,
                default => null,
            };
        } else {
            $this->license_plate_registration_fee = match ($this->license_plate_registration_option) {
                OrderMotorcycleLicensePlateRegistration::REGION_I => 4000000,
                OrderMotorcycleLicensePlateRegistration::REGION_II => 2000000,
                OrderMotorcycleLicensePlateRegistration::REGION_III => 1000000,
                default => null,
            };
        }
    }

    protected function handleHandlingFee(): void
    {
        $this->handling_fee = 0;
    }

    protected function handleTotal(): void
    {
        $this->total = $this->price +
            $this->motorcycle_registration_support_fee +
            $this->registration_fee +
            $this->license_plate_registration_fee +
            $this->handling_fee;
    }
}
