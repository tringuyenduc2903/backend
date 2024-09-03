<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;

class Role extends \Spatie\Permission\Models\Role
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
        'name',
        'guard_name',
    ];
}
