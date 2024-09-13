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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'published' => 'boolean',
        'specifications' => 'array',
    ];

    protected $with = [
        'options',
        'categories',
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
     * Determine if the model should be searchable.
     */
    public function shouldBeSearchable(): bool
    {
        return $this->published &&
            in_array(
                $this->getRawOriginal('visibility'), [
                    ProductVisibility::SEARCH,
                    ProductVisibility::CATALOG_AND_SEARCH,
                ]);
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

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */

    protected function getImagesAttribute(string|array|null $images = null): string|array|null
    {
        if (backpack_auth()->check()) {
            return $images;
        }

        $items = json_decode($images);

        foreach ($items as &$item) {
            $item = image_preview(
                product_image_url($item->image),
                $item->alt
            );
        }

        return array_values($items);
    }

    protected function getVideosAttribute(string|array|null $videos): string|array|null
    {
        if (backpack_auth()->check()) {
            return $videos;
        }

        $items = json_decode($videos);

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

    protected function getVisibilityAttribute(int $visibility): int|string
    {
        return backpack_auth()->check()
            ? $visibility
            : ProductVisibility::valueForKey($visibility);
    }

    protected function getTypeAttribute(int $type): int|string
    {
        return backpack_auth()->check()
            ? $type
            : ProductTypeEnum::valueForKey($type);
    }

    protected function getOptionsMinPriceAttribute(float $price): float|array
    {
        return backpack_auth()->check()
            ? $price
            : price_preview($price);
    }

    protected function getOptionsMaxPriceAttribute(float $price): float|array
    {
        return backpack_auth()->check()
            ? $price
            : price_preview($price);
    }
}
