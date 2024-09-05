<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Sushi\Sushi;

class Setting extends Model
{
    use CrudTrait;
    use Sushi;

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
        'value',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'fields',
        'validation_rules',
    ];

    protected array $rows = [[
        'key' => '',
        'name' => '',
        'value' => '',
        'fields' => '',
        'validation_rules' => '',
        'active' => '',
    ]];

    protected array $fakeColumns = [
        'value',
    ];
}
