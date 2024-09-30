<?php

namespace App\Models;

use App\Enums\OrderPaymentMethod;
use App\Enums\OrderStatus;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class OrderMotorcycle extends Model
{
    use CrudTrait;

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
        'address_id',
        'identification_id',
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

    protected function getPaymentMethodPreviewAttribute(): string
    {
        return OrderPaymentMethod::valueForKey($this->payment_method);
    }

    protected function getStatusPreviewAttribute(): string
    {
        return OrderStatus::valueForKey($this->status);
    }
}
