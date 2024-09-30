<?php

namespace App\Models;

use App\Enums\OrderMotorcycleLicensePlateRegistration;
use App\Enums\OrderMotorcycleRegistration;
use App\Enums\OrderPaymentMethod;
use App\Enums\OrderStatus;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class OrderMotorcycle extends Model
{
    use CrudTrait;
    use SwitchTimezoneTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'status',
        'note',
        'motorcycle_registration_support',
        'registration_option',
        'license_plate_registration_option',
        'payment_method',
        'payment_checkout_url',
        'amount',
        'option_id',
        'motor_cycle_id',
        'address_id',
        'identification_id',
        'customer_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'option_id',
        'motor_cycle_id',
        'customer_id',
        'address_id',
        'identification_id',
        'payment_method',
        'status',
        'price',
        'value_added_tax',
        'registration_option',
        'license_plate_registration_option',
        'motorcycle_registration_support_fee',
        'registration_fee',
        'license_plate_registration_fee',
        'tax',
        'handling_fee',
        'total',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'motorcycle_registration_support' => 'boolean',
    ];

    protected $appends = [
        'registration_option_preview',
        'license_plate_registration_option_preview',
        'payment_method_preview',
        'status_preview',
        'price_preview',
        'value_added_tax_preview',
        'motorcycle_registration_support_fee_preview',
        'registration_fee_preview',
        'license_plate_registration_fee_preview',
        'tax_preview',
        'handling_fee_preview',
        'total_preview',
    ];

    protected $with = [
        'option',
        'option.product',
    ];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    public function canCancel(): bool
    {
        return in_array($this->status, [
            OrderStatus::TO_PAY,
            OrderStatus::TO_SHIP,
            OrderStatus::TO_RECEIVE,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function option(): BelongsTo
    {
        return $this->belongsTo(Option::class)->withTrashed();
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class)->withTrashed();
    }

    public function identification(): BelongsTo
    {
        return $this->belongsTo(Identification::class)->withTrashed();
    }

    public function transactions(): MorphMany
    {
        return $this->morphMany(OrderTransaction::class, 'orderable');
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */

    protected function getPricePreviewAttribute(): array
    {
        return price_preview($this->price);
    }

    protected function getValueAddedTaxPreviewAttribute(): array
    {
        return percent_preview($this->value_added_tax);
    }

    protected function getMotorcycleRegistrationSupportFeePreviewAttribute(): array
    {
        return price_preview($this->motorcycle_registration_support_fee);
    }

    protected function getRegistrationFeePreviewAttribute(): array
    {
        return price_preview($this->registration_fee);
    }

    protected function getLicensePlateRegistrationFeePreviewAttribute(): array
    {
        return price_preview($this->license_plate_registration_fee);
    }

    protected function getTaxPreviewAttribute(): array
    {
        return price_preview($this->tax);
    }

    protected function getHandlingFeePreviewAttribute(): array
    {
        return price_preview($this->handling_fee);
    }

    protected function getTotalPreviewAttribute(): array
    {
        return price_preview($this->total);
    }

    protected function getRegistrationOptionPreviewAttribute(): ?string
    {
        return isset($this->registration_option)
            ? OrderMotorcycleRegistration::valueForKey($this->registration_option)
            : null;
    }

    protected function getLicensePlateRegistrationOptionPreviewAttribute(): ?string
    {
        return isset($this->license_plate_registration_option)
            ? OrderMotorcycleLicensePlateRegistration::valueForKey($this->license_plate_registration_option)
            : null;
    }

    protected function getPaymentMethodPreviewAttribute(): string
    {
        return OrderPaymentMethod::valueForKey($this->payment_method);
    }

    protected function getStatusPreviewAttribute(): string
    {
        return OrderStatus::valueForKey($this->status);
    }
}
