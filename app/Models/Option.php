<?php

namespace App\Models;

use App\Enums\OptionStatus;
use App\Enums\OptionType;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Option extends Model
{
    use CrudTrait;
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
        'sku',
        'price',
        'value_added_tax',
        'images',
        'color',
        'version',
        'volume',
        'type',
        'status',
        'quantity',
        'weight',
        'length',
        'width',
        'height',
        'specifications',
        'product_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'product_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'specifications' => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'parent');
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */

    protected function getPriceAttribute(float $price): float|array
    {
        return backpack_auth()->check()
            ? $price
            : price_preview($price);
    }

    protected function getValueAddedTaxAttribute(float $value_added_tax): float|array
    {
        return backpack_auth()->check()
            ? $value_added_tax
            : percent_preview($value_added_tax);
    }

    protected function getImagesAttribute(string|array|null $images = null): string|array|null
    {
        if (backpack_auth()->check()) {
            return $images;
        }

        $items = json_decode($images);

        if (! $images) {
            return $images;
        }

        foreach ($items as &$item) {
            $item = image_preview(
                product_image_url($item),
                $this->sku
            );
        }

        return array_values($items);
    }

    protected function getTypeAttribute(int $type): int|string
    {
        return backpack_auth()->check()
            ? $type
            : OptionType::valueForKey($type);
    }

    protected function getStatusAttribute(int $status): int|string
    {
        return backpack_auth()->check()
            ? $status
            : OptionStatus::valueForKey($status);
    }
}
