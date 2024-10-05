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
        'price',
        'value_added_tax',
        'images',
        'type',
        'status',
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

    protected $appends = [
        'price_preview',
        'value_added_tax_preview',
        'images_preview',
        'type_preview',
        'status_preview',
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

    protected function getPricePreviewAttribute(): array
    {
        return price_preview($this->price);
    }

    protected function getValueAddedTaxPreviewAttribute(): array
    {
        return percent_preview($this->value_added_tax);
    }

    protected function getImagesPreviewAttribute(): array
    {
        $items = json_decode($this->images);

        if ($items) {
            foreach ($items as &$item) {
                $item = image_preview(
                    product_image_url($item),
                    $this->sku
                );
            }
        }

        return array_values($items);
    }

    protected function getTypePreviewAttribute(): string
    {
        return OptionType::valueForKey($this->type);
    }

    protected function getStatusPreviewAttribute(): string
    {
        return OptionStatus::valueForKey($this->status);
    }
}
