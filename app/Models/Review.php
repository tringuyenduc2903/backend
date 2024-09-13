<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use RalphJSmit\Helpers\Laravel\Concerns\HasFactory;

class Review extends Model
{
    use CrudTrait;
    use HasFactory;

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
        'reviewable_id',
        'reviewable_type',
        'parent_id',
        'parent_type',
        'content',
        'rate',
        'images',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'reviewable_id',
        'reviewable_type',
        'parent_id',
        'parent_type',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function reviewable(): MorphTo
    {
        return $this->morphTo();
    }

    public function parent(): MorphTo
    {
        return $this->morphTo();
    }

    public function reply(): MorphOne
    {
        return $this->morphOne(Review::class, 'parent')
            ->where('reviewable_type', Employee::class)
            ->where('parent_type', Review::class);
    }
}
