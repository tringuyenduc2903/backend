<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

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
        'order_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'order_id',
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

    protected function getAmountPreviewAttribute(): array
    {
        return price_preview($this->amount);
    }

    protected function getStatusPreviewAttribute(): string
    {
        return OrderStatus::valueForKey($this->status);
    }
}
