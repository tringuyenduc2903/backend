<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use MongoDB\Laravel\Eloquent\SoftDeletes;
use RalphJSmit\Laravel\SEO\Support\HasSEO;

class Category extends Model
{
    use CrudTrait;
    use HasSEO;
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
        'image',
        'alt',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'image',
        'alt',
    ];

    protected $appends = [
        'image_preview',
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

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'categories_products');
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */

    protected function getImagePreviewAttribute(): ?array
    {
        return $this->image
            ? image_preview(
                category_image_url($this->image),
                $this->alt
            )
            : null;
    }
}
