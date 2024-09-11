<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SEO extends \RalphJSmit\Laravel\SEO\Models\SEO
{
    use CrudTrait;
    use SwitchTimezoneTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'robots' => 'json',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'model_type',
        'model_id',
        'product',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'model_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'model_id');
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */

    protected function getTitleAttribute(?string $title): ?string
    {
        if (backpack_auth()->check()) {
            return $title;
        }

        return match ($this->model_type) {
            Product::class => $title ?: $this->product->name,
            Category::class => $title ?: $this->category->name,
            default => $title,
        };
    }

    protected function getImageAttribute(?string $image): ?string
    {
        if (backpack_auth()->check()) {
            return $image;
        }

        return match ($this->model_type) {
            Product::class => $image
                ? product_image_url($image)
                : $this->product->images[0]['url'],
            Category::class => $image
                ? product_image_url($image)
                : $this->category->image,
            default => product_image_url($image),
        };
    }
}
