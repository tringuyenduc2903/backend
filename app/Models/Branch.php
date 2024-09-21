<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
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
        'name',
        'phone_number',
        'image',
        'alt',
        'country',
        'province_id',
        'district_id',
        'ward_id',
        'address_detail',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'image',
        'alt',
        'province_id',
        'district_id',
        'ward_id',
    ];

    protected $appends = [
        'image_preview',
        'address_preview',
    ];

    protected $with = [
        'province',
        'district',
        'ward',
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

    protected function getImagePreviewAttribute(): ?array
    {
        return $this->image
            ? image_preview(
                branch_image_url($this->image),
                $this->alt
            )
            : null;
    }
}
