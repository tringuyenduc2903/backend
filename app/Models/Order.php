<?php

namespace App\Models;

use App\Enums\OrderPaymentMethod;
use App\Enums\OrderShippingMethod;
use App\Enums\OrderStatus;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
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
        'shipping_method',
        'payment_method',
        'other_fields',
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
        'address_id',
        'identification_id',
        'customer_id',
        'shipping_method',
        'payment_method',
        'status',
        'tax',
        'shipping_fee',
        'handling_fee',
        'total',
        'other_fields',
        'other_fees',
    ];

    protected $appends = [
        'shipping_method_preview',
        'payment_method_preview',
        'status_preview',
        'tax_preview',
        'shipping_fee_preview',
        'handling_fee_preview',
        'total_preview',
        'other_fields_preview',
        'other_fees_preview',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function options(): HasMany
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function option(): HasOne
    {
        return $this->hasOne(OrderProduct::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class)->withTrashed();
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(OrderShipments::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(OrderTransaction::class);
    }

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
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */

    protected function getTaxPreviewAttribute(): array
    {
        return price_preview($this->tax);
    }

    protected function getShippingFeePreviewAttribute(): array
    {
        return price_preview($this->shipping_fee);
    }

    protected function getHandlingFeePreviewAttribute(): array
    {
        return price_preview($this->handling_fee);
    }

    protected function getTotalPreviewAttribute(): array
    {
        return price_preview($this->total);
    }

    protected function getShippingMethodPreviewAttribute(): string
    {
        return OrderShippingMethod::valueForKey($this->shipping_method);
    }

    protected function getPaymentMethodPreviewAttribute(): string
    {
        return OrderPaymentMethod::valueForKey($this->payment_method);
    }

    protected function getStatusPreviewAttribute(): string
    {
        return OrderStatus::valueForKey($this->status);
    }

    protected function getOtherFieldsPreviewAttribute(): array
    {
        $items = json_decode($this->other_fields);

        return array_values($items);
    }

    protected function getOtherFeesPreviewAttribute(): array
    {
        $items = json_decode($this->other_fees);

        return array_values($items);
    }
}
