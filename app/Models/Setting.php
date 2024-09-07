<?php

namespace App\Models;

use App\Eloquent\Model;
use Backpack\CRUD\app\Models\Traits\CrudTrait;

class Setting extends Model
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

    protected $connection = 'mongodb';

    protected array $fakeColumns = [
        'value',
    ];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    public static function get(string $key): Setting
    {
        return static::whereKey($key)->firstOrFail();
    }
}
