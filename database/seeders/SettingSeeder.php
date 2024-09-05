<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Setting::whereName('')->first()?->delete();

        foreach ([
            $this->homepageBanners(),
            $this->headerMenu(),
        ] as $row) {
            Setting::updateOrCreate($row['attributes'], $row['values']);
        }
    }

    protected function homepageBanners(): array
    {
        return [
            'attributes' => [
                'key' => 'homepage_banners',
            ],
            'values' => [
                'name' => trans('Banners'),
                'fields' => json_encode([[
                    'name' => 'show_navigation_button',
                    'label' => trans('Show navigation button'),
                    'type' => 'switch',
                    'fake' => true,
                    'store_in' => 'value',
                ], [
                    'name' => 'show_page_number',
                    'label' => trans('Show page number'),
                    'type' => 'switch',
                    'fake' => true,
                    'store_in' => 'value',
                ], [
                    'name' => 'automatically_switch_banners',
                    'label' => trans('Automatically switch banners'),
                    'type' => 'switch',
                    'fake' => true,
                    'store_in' => 'value',
                ], [
                    'name' => 'time_to_automatically_switch_banners',
                    'label' => trans('Time to automatically switch banners'),
                    'type' => 'number',
                    'prefix' => 'ms',
                    'fake' => true,
                    'store_in' => 'value',
                ], [
                    'name' => 'banners',
                    'label' => trans('Banners'),
                    'type' => 'repeatable',
                    'fake' => true,
                    'store_in' => 'value',
                    'subfields' => [[
                        'name' => 'image',
                        'label' => trans('Image'),
                        'type' => 'url',
                        'wrapper' => [
                            'class' => 'form-group col-sm-12 col-md-6',
                        ],
                    ], [
                        'name' => 'alt',
                        'label' => trans('Alt text'),
                        'wrapper' => [
                            'class' => 'form-group col-sm-12 col-md-6',
                        ],
                    ], [
                        'name' => 'subtitle',
                        'label' => trans('Subtitle'),
                        'wrapper' => [
                            'class' => 'form-group col-sm-12 col-md-6',
                        ],
                    ], [
                        'name' => 'title',
                        'label' => trans('Title'),
                        'wrapper' => [
                            'class' => 'form-group col-sm-12 col-md-6',
                        ],
                    ], [
                        'name' => 'description',
                        'label' => trans('Description'),
                        'type' => 'textarea',
                    ], [
                        'name' => 'page_name',
                        'label' => trans('Page name'),
                        'wrapper' => [
                            'class' => 'form-group col-sm-12 col-md-6',
                        ],
                    ], [
                        'name' => 'banner_description',
                        'label' => trans('Banner description'),
                        'wrapper' => [
                            'class' => 'form-group col-sm-12 col-md-6',
                        ],
                    ], [
                        'name' => 'actions',
                        'label' => trans('Actions'),
                        'type' => 'table',
                        'columns' => [
                            'title' => trans('Title'),
                            'link' => trans('Link'),
                        ],
                        'max' => 2,
                    ]],
                    'min_rows' => 1,
                    'max_rows' => 15,
                ]], JSON_UNESCAPED_UNICODE),
                'active' => true,
                'validation_rules' => json_encode([
                    'show_navigation_button' => [
                        'required',
                        'boolean',
                    ],
                    'show_page_number' => [
                        'required',
                        'boolean',
                    ],
                    'automatically_switch_banners' => [
                        'required',
                        'boolean',
                    ],
                    'time_to_automatically_switch_banners' => [
                        'nullable',
                        'required_if:automatically_switch_banners,true',
                        'integer',
                        'between:0,10000',
                    ],
                    'banners' => [
                        'required',
                        'array',
                        'max:15',
                    ],
                    'banners.*.image' => [
                        'nullable',
                        'string',
                        'max:100',
                    ],
                    'banners.*.alt' => [
                        'nullable',
                        'required_with:banners.*.image',
                        'string',
                        'max:50',
                    ],
                    'banners.*.subtitle' => [
                        'required',
                        'string',
                        'max:50',
                    ],
                    'banners.*.title' => [
                        'required',
                        'string',
                        'max:50',
                    ],
                    'banners.*.description' => [
                        'required',
                        'string',
                        'max:255',
                    ],
                    'banners.*.page_name' => [
                        'required',
                        'string',
                        'max:50',
                    ],
                    'banners.*.banner_description' => [
                        'required',
                        'string',
                        'max:50',
                    ],
                    'banners.*.actions' => [
                        'actions',
                    ],
                ], JSON_UNESCAPED_UNICODE),
            ],
        ];
    }

    protected function headerMenu(): array
    {
        return [
            'attributes' => [
                'key' => 'header_menu',
            ],
            'values' => [
                'name' => trans('Title'),
                'fields' => json_encode([[
                    'name' => 'title',
                    'label' => trans('Title'),
                    'fake' => true,
                    'store_in' => 'value',
                ], [
                    'name' => 'description',
                    'label' => trans('Description'),
                    'type' => 'textarea',
                    'fake' => true,
                    'store_in' => 'value',
                ]], JSON_UNESCAPED_UNICODE),
                'active' => true,
                'validation_rules' => json_encode([
                    'title' => [
                        'required',
                        'string',
                        'max:50',
                    ],
                    'description' => [
                        'required',
                        'string',
                        'max:255',
                    ],
                ], JSON_UNESCAPED_UNICODE),
            ],
        ];
    }
}
