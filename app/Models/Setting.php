<?php

namespace App\Models;

use App\Eloquent\Model;
use Backpack\CRUD\app\Models\Traits\CrudTrait;

class Setting extends Model
{
    use CrudTrait;
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
        'name',
        'value',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'value',
        'fields',
        'validation_rules',
    ];

    protected $appends = [
        'value_preview',
    ];

    protected $connection = 'mongodb';

    protected array $fakeColumns = [
        'value',
    ];

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */

    protected function getValuePreviewAttribute(): mixed
    {
        switch ($this->key) {
            case 'homepage_banners':
                $reformat = json_decode($this->value);

                foreach ([
                    'show_navigation_button',
                    'show_page_number',
                    'automatically_switch_banners',
                ] as $item) {
                    $reformat->$item = (bool) $reformat->$item;
                }

                $reformat->time_to_automatically_switch_banners = (int) $reformat->time_to_automatically_switch_banners;

                foreach ($reformat->banners as $banner) {
                    $banner->image = image_preview(
                        $banner->image,
                        $banner->alt
                    );

                    unset($banner->alt);

                    $banner->actions = json_decode($banner->actions);
                }

                return $reformat;
            case 'footer_about':
            case 'footer_services':
                return json_decode($this->value);
            case 'footer_branch':
                return Branch::findOrFail($this->value);
            case 'auth_small_banner':
            case 'auth_large_banner':
                $reformat = json_decode($this->value);

                $reformat->image = image_preview(
                    $reformat->image,
                    $reformat->alt
                );

                unset($reformat->alt);

                return $reformat;
            case 'store_ghn':
                $reformat = json_decode($this->value);

                $reformat->district = District::whereGhnId($reformat->district_id)->firstOrFail();

                unset(
                    $reformat->district_id,
                    $reformat->shop_id,
                );

                return $reformat;
            case 'store_currency':
                return json_decode($this->value)->value;
            default:
                return $this->value;
        }
    }
}
