<?php

namespace App\Models;

use App\Enums\GhnOrderReason;
use App\Enums\GhnOrderStatus;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class OrderShipments extends Model
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
        'name',
        'description',
        'reason',
        'order_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'order_id',
        'name',
        'reason',
    ];

    protected $appends = [
        'name_preview',
        'reason_preview',
    ];

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */

    protected function getNamePreviewAttribute(): string
    {
        return GhnOrderStatus::valueForKey($this->name);
    }

    protected function getReasonPreviewAttribute(): ?string
    {
        return $this->reason
            ? GhnOrderReason::valueForKey($this->reason)
            : null;
    }
}
