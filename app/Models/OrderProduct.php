<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderProduct extends Model
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
        'order_id',
        'option_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'order_id',
        'option_id',
        'price',
        'value_added_tax',
    ];

    protected $appends = [
        'price_preview',
        'value_added_tax_preview',
    ];

    protected $with = [
        'option',
        'option.product',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function option(): BelongsTo
    {
        return $this->belongsTo(Option::class)->withTrashed();
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
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
}
