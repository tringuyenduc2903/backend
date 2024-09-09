<?php

namespace App\Models;

use App\Enums\CustomerAddress;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use CrudTrait;
    use HasFactory;
    use SoftDeletes;
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
        'customer_name',
        'customer_phone_number',
        'country',
        'province_id',
        'district_id',
        'ward_id',
        'address_detail',
        'type',
        'default',
        'customer_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'province_id',
        'district_id',
        'ward_id',
        'customer_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'default' => 'boolean',
    ];

    protected $appends = [
        'type_preview',
        'address_preview',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class);
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */

    protected function getTypePreviewAttribute(): string
    {
        return CustomerAddress::valueForKey($this->type);
    }

    protected function getAddressPreviewAttribute(): string
    {
        return sprintf(
            '%s, %s, %s, %s (%s)',
            $this->address_detail,
            $this->ward?->name,
            $this->district?->name,
            $this->province?->name,
            $this->country
        );
    }
}
