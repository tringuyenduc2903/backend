<?php

namespace App\Models;

use App\Enums\OrderTransactionStatus;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderTransaction extends Model
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
        'amount',
        'status',
        'reference',
        'orderable_id',
        'orderable_type',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'orderable_id',
        'orderable_type',
        'amount',
        'status',
    ];

    protected $appends = [
        'amount_preview',
        'status_preview',
    ];

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'orderable_id');
    }

    public function order_motorcycle(): BelongsTo
    {
        return $this->belongsTo(OrderMotorcycle::class, 'orderable_id');
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    protected function getAmountPreviewAttribute(): array
    {
        return price_preview($this->amount);
    }

    protected function getStatusPreviewAttribute(): string
    {
        return OrderTransactionStatus::valueForKey($this->status);
    }
}
