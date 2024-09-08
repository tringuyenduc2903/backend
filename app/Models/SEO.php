<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;

class SEO extends \RalphJSmit\Laravel\SEO\Models\SEO
{
    use CrudTrait;

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
        'robots' => 'array',
    ];
}
