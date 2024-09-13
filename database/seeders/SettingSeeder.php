<?php

namespace Database\Seeders;

use App\Actions\GiaoHangNhanh\StoreCache;
use App\Enums\ProductType;
use App\Enums\ProductTypeEnum;
use App\Enums\ProductVisibility;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class SettingSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $product = Product::inRandomOrder()
            ->whereType(ProductType::MOTOR_CYCLE)
            ->wherePublished(true)
            ->whereNot('visibility', ProductVisibility::NOT_VISIBLE_INDIVIDUALLY)
            ->whereJsonLength('images', '>=', 2)
            ->inRandomOrder();

        $products = $product->clone()->take(5)->get();
        $banner = $product->clone()->first()->images;
        $auth_small_banner = $banner[0];
        $auth_large_banner = $banner[1];

        foreach ([
            $this->homepageBanners($products),
            $this->footerAbout(),
            $this->footerServices(),
            $this->footerBranch(),
            $this->authBanner('auth_small_banner', $auth_small_banner),
            $this->authBanner('auth_large_banner', $auth_large_banner),
            $this->storeGhn(),
            $this->storeCurrency(),
        ] as $row) {
            Setting::updateOrCreate($row['attributes'], $row['values']);
        }
    }

    protected function homepageBanners(Collection $products): array
    {
        return [
            'attributes' => [
                'key' => 'homepage_banners',
            ],
            'values' => [
                'name' => trans('Banners'),
                'fields' => [[
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
                ]],
                'value' => json_encode([
                    'show_navigation_button' => true,
                    'show_page_number' => true,
                    'automatically_switch_banners' => true,
                    'time_to_automatically_switch_banners' => 5000,
                    'banners' => $products->map(
                        function (Product $product): array {
                            $image = $product->images[1];

                            return [
                                'image' => $image['url'],
                                'alt' => $image['alt'],
                                'subtitle' => $product->manufacturer,
                                'title' => $product->name,
                                'description' => $product->seo->description,
                                'page_name' => ProductType::valueForKey($product->getRawOriginal('type')),
                                'banner_description' => implode(' | ', $product->categories()->pluck('name')->toArray()),
                                'actions' => [[
                                    'title' => trans('See details'),
                                    'link' => sprintf(
                                        'products/%s/%s',
                                        ProductTypeEnum::MOTOR_CYCLE->value,
                                        $product->search_url
                                    ),
                                ], [
                                    'title' => trans('See more products', [
                                        'name' => $product->manufacturer,
                                    ]),
                                    'link' => sprintf(
                                        'products/%s?manufacturer=%s',
                                        ProductTypeEnum::MOTOR_CYCLE->value,
                                        $product->manufacturer
                                    ),
                                ]],
                            ];
                        }
                    ),
                ], JSON_UNESCAPED_UNICODE),
                'active' => true,
                'validation_rules' => [
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
                        'max:500',
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
                ],
            ],
        ];
    }

    protected function footerAbout(): array
    {
        return [
            'attributes' => [
                'key' => 'footer_about',
            ],
            'values' => [
                'name' => trans('Time in works'),
                'fields' => [[
                    'name' => 'description',
                    'label' => trans('Description'),
                    'type' => 'textarea',
                    'fake' => true,
                    'store_in' => 'value',
                ], [
                    'name' => 'work_schedules',
                    'label' => trans('Work schedules'),
                    'fake' => true,
                    'store_in' => 'value',
                    'type' => 'repeatable',
                    'subfields' => [[
                        'name' => 'title',
                        'label' => trans('Title'),
                        'wrapper' => [
                            'class' => 'form-group col-sm-12 col-md-6',
                        ],
                    ], [
                        'name' => 'description',
                        'label' => trans('Description'),
                        'wrapper' => [
                            'class' => 'form-group col-sm-12 col-md-6',
                        ],
                    ]],
                    'min_rows' => 1,
                    'max_rows' => 3,
                ]],
                'value' => json_encode([
                    'description' => mb_substr(
                        vnfaker()->paragraphs(2, glue: ' '),
                        0,
                        254
                    ),
                    'work_schedules' => [[
                        'title' => trans('Monday - Friday'),
                        'description' => trans('9am to 5pm'),
                    ], [
                        'title' => trans('Saturday'),
                        'description' => trans('10am to 2pm'),
                    ]],
                ], JSON_UNESCAPED_UNICODE),
                'active' => true,
                'validation_rules' => [
                    'description' => [
                        'required',
                        'string',
                        'max:255',
                    ],
                    'work_schedules' => [
                        'required',
                        'array',
                        'max:3',
                    ],
                    'work_schedules.*.title' => [
                        'required',
                        'string',
                        'max:50',
                    ],
                    'work_schedules.*.description' => [
                        'required',
                        'string',
                        'max:50',
                    ],
                ],
            ],
        ];
    }

    protected function footerServices(): array
    {
        return [
            'attributes' => [
                'key' => 'footer_services',
            ],
            'values' => [
                'name' => trans('Services'),
                'fields' => [[
                    'name' => 'value',
                    'label' => trans('Value'),
                    'type' => 'repeatable',
                    'subfields' => [[
                        'name' => 'title',
                        'label' => trans('Title'),
                        'wrapper' => [
                            'class' => 'form-group col-sm-12 col-md-6',
                        ],
                    ], [
                        'name' => 'link',
                        'label' => trans('Link'),
                        'wrapper' => [
                            'class' => 'form-group col-sm-12 col-md-6',
                        ],
                    ]],
                    'min_rows' => 1,
                    'max_rows' => 5,
                ]],
                'value' => json_encode([[
                    'title' => trans('Motor cycle'),
                    'link' => 'products/'.ProductTypeEnum::MOTOR_CYCLE->value,
                ], [
                    'title' => trans('Square parts'),
                    'link' => 'products/'.ProductTypeEnum::SQUARE_PARTS->value,
                ], [
                    'title' => trans('Accessories'),
                    'link' => 'products/'.ProductTypeEnum::ACCESSORIES->value,
                ]], JSON_UNESCAPED_UNICODE),
                'active' => true,
                'validation_rules' => [
                    'value' => [
                        'required',
                        'array',
                        'max:5',
                    ],
                    'value.*.title' => [
                        'required',
                        'string',
                        'max:50',
                    ],
                    'value.*.link' => [
                        'required',
                        'string',
                        'max:255',
                    ],
                ],
            ],
        ];
    }

    protected function footerBranch(): array
    {
        return [
            'attributes' => [
                'key' => 'footer_branch',
            ],
            'values' => [
                'name' => trans('Branch'),
                'fields' => [[
                    'name' => 'value',
                    'label' => trans('Value'),
                    'type' => 'select2_from_ajax',
                    'model' => Branch::class,
                    'data_source' => route('employees.fetchBranches'),
                    'minimum_input_length' => 0,
                    'method' => 'POST',
                ]],
                'value' => Branch::inRandomOrder()->first()->id,
                'active' => true,
                'validation_rules' => [
                    'value' => [
                        'required',
                        'integer',
                        sprintf('exists:%s,%s', Branch::class, 'id'),
                    ],
                ],
            ],
        ];
    }

    protected function authBanner(string $column, array $image): array
    {
        return [
            'attributes' => [
                'key' => $column,
            ],
            'values' => [
                'name' => trans('Banner'),
                'fields' => [[
                    'name' => 'image',
                    'label' => trans('Image'),
                    'type' => 'image',
                    'crop' => true,
                    'withFiles' => [
                        'disk' => 'setting',
                    ],
                    'fake' => true,
                    'store_in' => 'value',
                ], [
                    'name' => 'alt',
                    'label' => trans('Alt text'),
                    'fake' => true,
                    'store_in' => 'value',
                ]],
                'value' => json_encode([
                    'image' => $image['url'],
                    'alt' => $image['alt'],
                ], JSON_UNESCAPED_UNICODE),
                'active' => true,
                'validation_rules' => [
                    'image' => [
                        'sometimes',
                        'image_banner',
                    ],
                    'alt' => [
                        'nullable',
                        'required_with:image',
                        'string',
                        'max:50',
                    ],
                ],
            ],
        ];
    }

    protected function storeGhn(): array
    {
        $shops = [];

        foreach (app(StoreCache::class)->stores()['shops'] as $shop) {
            $key = json_encode([
                'district_id' => $shop['district_id'],
                'shop_id' => $shop['_id'],
            ], JSON_UNESCAPED_UNICODE);

            $shops[$key] = $shop['name'];
        }

        return [
            'attributes' => [
                'key' => 'store_ghn',
            ],
            'values' => [
                'name' => trans('Shop at GHN'),
                'fields' => [[
                    'name' => 'value',
                    'label' => trans('Value'),
                    'type' => 'select2_from_array',
                    'options' => $shops,
                    'allows_null' => false,
                ]],
                'value' => json_encode([
                    'value' => array_key_first($shops),
                ], JSON_UNESCAPED_UNICODE),
                'active' => true,
                'validation_rules' => [
                    'value' => [
                        'required',
                        'string',
                    ],
                ],
            ],
        ];
    }

    protected function storeCurrency(): array
    {
        return [
            'attributes' => [
                'key' => 'store_currency',
            ],
            'values' => [
                'name' => trans('Currency'),
                'fields' => [[
                    'name' => 'value',
                    'label' => trans('Value'),
                    'fake' => true,
                    'store_in' => 'value',
                ]],
                'value' => json_encode([
                    'value' => 'VND',
                ], JSON_UNESCAPED_UNICODE),
                'active' => true,
                'validation_rules' => [
                    'value' => [
                        'required',
                        'string',
                        'max:3',
                    ],
                ],
            ],
        ];
    }
}
