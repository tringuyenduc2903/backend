<?php

namespace App\Models;

use App\Enums\OrderPaymentMethod;
use App\Enums\OrderShippingMethod;
use App\Enums\OrderStatus;
use App\Enums\OrderTransactionStatus;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

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
        'address_id',
        'customer_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'address_id',
        'customer_id',
        'shipping_method',
        'payment_method',
        'status',
        'tax',
        'shipping_fee',
        'handling_fee',
        'total',
    ];

    protected $appends = [
        'shipping_method_preview',
        'payment_method_preview',
        'status_preview',
        'tax_preview',
        'shipping_fee_preview',
        'handling_fee_preview',
        'total_preview',
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

    public function canCancel(): bool
    {
        return in_array($this->status, [
            OrderStatus::TO_PAY,
            OrderStatus::TO_SHIP,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    public function canCreateGhnOrder(): bool
    {
        return $this->status == OrderStatus::TO_SHIP &&
            $this->shipping_method == OrderShippingMethod::DOOR_TO_DOOR_DELIVERY &&
            is_null($this->shipping_code);
    }

    public function canAddTransaction(): bool
    {
        return $this->status == OrderStatus::TO_PAY &&
            $this->payment_method == OrderPaymentMethod::PAYMENT_ON_DELIVERY;
    }

    public function canProductHandover(): bool
    {
        return $this->status == OrderStatus::TO_RECEIVE &&
            $this->shipping_method == OrderShippingMethod::PICKUP_AT_STORE;
    }

    protected function getPaidAttribute(): float
    {
        return $this
            ->transactions()
            ->whereStatus(OrderTransactionStatus::PAID)
            ->sum('amount');
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */

    public function transactions(): MorphMany
    {
        return $this->morphMany(OrderTransaction::class, 'orderable');
    }

    protected function getToBePaidAttribute(): float
    {
        return $this->total - $this->paid;
    }

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
}
