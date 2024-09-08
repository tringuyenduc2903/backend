<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;
use RalphJSmit\Laravel\SEO\Support\HasSEO;

class Category extends Model
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
        'image',
        'alt',
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
}
