<?php

namespace App\Models;

use App\Enums\ProductListsType;
use App\Enums\ProductTypeEnum;
use App\Enums\ProductVisibility;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use RalphJSmit\Laravel\SEO\Support\HasSEO;

class Product extends Model
{
    use CrudTrait;
    use HasSEO;
    use Searchable;
    use Sluggable;
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
        'search_url',
        'name',
        'description',
        'images',
        'videos',
        'published',
        'visibility',
        'type',
        'manufacturer',
        'specifications',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'images',
        'videos',
        'visibility',
        'type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'published' => 'boolean',
        'specifications' => 'array',
    ];

    protected $with = [
        'categories',
    ];

    protected $appends = [
        'images_preview',
        'videos_preview',
        'visibility_preview',
        'type_preview',
    ];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    /**
     * Return the sluggable configuration array for this model.
     */
    public function sluggable(): array
    {
        return [
            'search_url' => [
                'source' => 'name',
            ],
        ];
    }

    /**
     * Get the indexable data array for the model.
     */
    public function toSearchableArray(): array
    {
        return $this
            ->with('options')
            ->withMin('options', 'price')
            ->withAvg('reviews', 'rate')
            ->findOrFail($this->id)
            ->toArray();
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function options(): HasMany
    {
        return $this->hasMany(Option::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'categories_products');
    }

    public function upsell(): BelongsToMany
    {
        return $this->product_lists()->withPivotValue(
            'type',
            ProductListsType::UPSELL
        );
    }

    public function product_lists(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                Product::class,
                'product_lists',
                'parent_id',
                'children_id'
            )
            ->withMin('options', 'price')
            ->withMax('options', 'price');
    }

    public function cross_sell(): BelongsToMany
    {
        return $this->product_lists()->withPivotValue(
            'type',
            ProductListsType::CROSS_SELL
        );
    }

    public function related_products(): BelongsToMany
    {
        return $this->product_lists()->withPivotValue(
            'type',
            ProductListsType::RELATED_PRODUCTS
        );
    }

    public function reviews(): HasManyThrough
    {
        return $this->hasManyThrough(
            Review::class,
            Option::class,
            'product_id',
            'parent_id',
            'id',
            'id'
        )->where(
            'parent_type',
            Option::class
        )->latest();
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */

    protected function getImagesPreviewAttribute(): array
    {
        $items = json_decode($this->images);

        if ($items) {
            foreach ($items as &$item) {
                $item = image_preview(
                    product_image_url($item->image),
                    $item->alt
                );
            }
        }

        return array_values($items);
    }

    protected function getVideosPreviewAttribute(): array
    {
        $items = json_decode($this->videos);

        foreach ($items as $item) {
            $item->video = json_decode($item->video);

            if ($item->title) {
                $item->video->title = $item->title;
            }

            if ($item->description) {
                $item->video->description = $item->description;
            }

            $item->video->image = image_preview(
                $item->image
                    ? product_image_url($item->image)
                    : $item->video->image,
                $item->video->title
            );

            unset($item->title, $item->description, $item->image);
        }

        return $items;
    }

    protected function getVisibilityPreviewAttribute(): string
    {
        return ProductVisibility::valueForKey($this->visibility);
    }

    protected function getTypePreviewAttribute(): string
    {
        return ProductTypeEnum::valueForKey($this->type);
    }

    protected function getOptionsMinPriceAttribute(float $price): array
    {
        return price_preview($price);
    }

    protected function getOptionsMaxPriceAttribute(float $price): array
    {
        return price_preview($price);
    }

    protected function getReviewsAvgRateAttribute(?float $rate): float
    {
        return $rate ?: 0;
    }
}
