<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Review extends Model
{
    use CrudTrait;

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

    public function reply(): MorphOne
    {
        return $this->morphOne(Review::class, 'parent')
            ->where('parent_type', Review::class)
            ->where('reviewable_type', User::class);
    }
}
