<?php

namespace App\Models;

use App\Enums\ProductListsType;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use RalphJSmit\Laravel\SEO\Support\HasSEO;

class Product extends Model
{
    use CrudTrait;
    use HasSEO;
    use Sluggable;
    use SoftDeletes;

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
        'enabled',
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
        'specifications' => 'array',
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
        return $this->belongsToMany(
            Product::class,
            'product_lists',
            'parent_id',
            'children_id'
        );
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
}
