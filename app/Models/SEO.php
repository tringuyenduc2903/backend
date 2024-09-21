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
        'title',
        'image',
    ];

    protected $appends = [
        'title_preview',
        'image_preview',
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

    protected function getTitlePreviewAttribute(): ?string
    {
        return $this->title ?: match ($this->model_type) {
            Product::class => $this->product->name,
            Category::class => $this->category->name,
            default => $this->title,
        };
    }

    protected function getImagePreviewAttribute(): ?string
    {
        return match ($this->model_type) {
            Product::class => product_image_url(
                $this->image ?: ($this->product->images[0]['url'] ?? '')
            ),
            Category::class => category_image_url(
                $this->image ?: ($this->category->image ?? '')
            ),
            default => $this->image,
        };
    }
}
