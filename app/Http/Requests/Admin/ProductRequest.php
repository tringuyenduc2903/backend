<?php

namespace App\Http\Requests\Admin;

use App\Enums\OptionStatus;
use App\Enums\OptionType;
use App\Enums\ProductType;
use App\Enums\ProductVisibility;
use App\Models\Category;
use App\Models\Option;
use App\Models\Product;
use App\Rules\Image;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // only allow updates if the user is logged in
        return backpack_auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $id = $this->input('id') ?? request()->route('id');
        $type = $this->input('type');

        return [
            'published' => [
                'required',
                'boolean',
            ],
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
                'max:4294967295',
            ],
            'categories' => [
                'nullable',
                'sometimes',
                'array',
                Rule::exists(Category::class, 'id'),
            ],
            'visibility' => [
                'required',
                'integer',
                Rule::in(ProductVisibility::keys()),
            ],
            'type' => [
                'required',
                'integer',
                Rule::in(ProductType::keys()),
            ],
            'manufacturer' => [
                'required',
                'string',
                'max:50',
            ],
            'specifications' => [
                'nullable',
                'array',
                'max:40',
            ],
            'specifications.*.title' => [
                'required',
                'string',
                'max:50',
            ],
            'specifications.*.description' => [
                'required',
                'string',
                'max:255',
            ],
            'search_url' => [
                $id ? 'required' : 'sometimes',
                'string',
                'max:255',
                Rule::unique(Product::class)->ignore($id),
            ],
            'seo' => [
                'sometimes',
                'array',
                'max:1',
            ],
            'seo.*.title' => [
                'nullable',
                'string',
                'max:60',
            ],
            'seo.*.description' => [
                'nullable',
                'required_with:description',
                'string',
                'max:160',
            ],
            'seo.*.image' => [
                'nullable',
                app(Image::class),
            ],
            'seo.*.author' => [
                'nullable',
                'string',
                'max:50',
            ],
            'seo.*.robots' => [
                function ($attribute, $value, $fail) {
                    $items = json_decode($value, true);

                    $validator = Validator::make([
                        'robots' => $items,
                    ], [
                        'robots' => [
                            'nullable',
                            'array',
                            'max:5',
                        ],
                        'robots.*.name' => [
                            'required',
                            'string',
                            'max:50',
                        ],
                        'robots.*.value' => [
                            'required',
                            'string',
                            'max:255',
                        ],
                    ]);

                    if ($validator->fails()) {
                        $fail($validator->errors()->first());
                    }
                },
            ],
            'options' => [
                'required',
                'array',
            ],
            'options.*.sku' => [
                'required',
                'string',
                'max:50',
                function ($attribute, $value, $fail) {
                    $validator = Validator::make([
                        'sku' => $value,
                    ], [
                        'sku' => Rule::unique(Option::class)->ignore($this->input(
                            str_replace('sku', 'id', $attribute)
                        )),
                    ]);

                    if ($validator->fails()) {
                        $fail($validator->errors()->first());
                    }
                },
            ],
            'options.*.price' => [
                'required',
                'decimal:0,2',
                'between:0,9999999999',
            ],
            'options.*.value_added_tax' => [
                'required',
                'integer',
                'between:0,10',
            ],
            'options.*.images' => [
                function ($attribute, $value, $fail) {
                    $items = json_decode($value, true);

                    $validator = Validator::make([
                        'images' => $items,
                    ], [
                        'images' => [
                            'array',
                            'max:10',
                        ],
                        'images.*' => [
                            'required',
                            'string',
                            function ($attribute, $value, $fail) {
                                if (str_contains($value, CRUD::get('dropzone.temporary_folder'))) {
                                    if (! Storage::disk(CRUD::get('dropzone.temporary_disk'))->exists($value)) {
                                        $fail(trans('validation.image'));
                                    }
                                } elseif (! Storage::disk('product')->exists($value)) {
                                    $fail(trans('validation.image'));
                                }
                            },
                        ],
                    ]);

                    if ($validator->fails()) {
                        $fail($validator->errors()->first());
                    }
                },
            ],
            'options.*.color' => [
                'nullable',
                Rule::requiredIf($type == ProductType::MOTOR_CYCLE),
                'string',
                'max:50',
            ],
            'options.*.version' => [
                'nullable',
                Rule::requiredIf($type == ProductType::MOTOR_CYCLE),
                'string',
                'max:50',
            ],
            'options.*.volume' => [
                'nullable',
                'string',
                'max:50',
            ],
            'options.*.type' => [
                'required',
                'integer',
                Rule::in(OptionType::keys()),
            ],
            'options.*.status' => [
                'required',
                'integer',
                Rule::in(OptionStatus::keys()),
            ],
            'options.*.quantity' => [
                'required',
                'integer',
                'between:0,65535',
            ],
            'options.*.weight' => [
                'nullable',
                Rule::requiredIf(in_array(
                    $type, [
                        ProductType::SQUARE_PARTS, ProductType::ACCESSORIES,
                    ])),
                'integer',
                'between:1,4294967295',
            ],
            'options.*.length' => [
                'nullable',
                Rule::requiredIf(in_array(
                    $type, [
                        ProductType::SQUARE_PARTS, ProductType::ACCESSORIES,
                    ])),
                'integer',
                'between:1,4294967295',
            ],
            'options.*.width' => [
                'nullable',
                Rule::requiredIf(in_array(
                    $type, [
                        ProductType::SQUARE_PARTS, ProductType::ACCESSORIES,
                    ])),
                'integer',
                'between:1,4294967295',
            ],
            'options.*.height' => [
                'nullable',
                Rule::requiredIf(in_array(
                    $type, [
                        ProductType::SQUARE_PARTS, ProductType::ACCESSORIES,
                    ])),
                'integer',
                'between:1,4294967295',
            ],
            'options.*.specifications' => [
                function ($attribute, $value, $fail) {
                    $validator = Validator::make([
                        'specifications' => json_decode($value, true),
                    ], [
                        'specifications' => [
                            'nullable',
                            'array',
                            'max:40',
                        ],
                        'specifications.*.title' => [
                            'required',
                            'string',
                            'max:50',
                        ],
                        'specifications.*.description' => [
                            'required',
                            'string',
                            'max:255',
                        ],
                    ]);

                    if ($validator->fails()) {
                        $fail($validator->errors()->first());
                    }
                },
            ],
            'upsell' => [
                'nullable',
                'sometimes',
                'array',
                Rule::exists(Product::class, 'id')->whereNot('id', $id),
            ],
            'cross_sell' => [
                'nullable',
                'sometimes',
                'array',
                Rule::exists(Product::class, 'id')->whereNot('id', $id),
            ],
            'related_products' => [
                'nullable',
                'sometimes',
                'array',
                Rule::exists(Product::class, 'id')->whereNot('id', $id),
            ],
            'images' => [
                'nullable',
                'array',
                'max:15',
            ],
            'images.*.image' => [
                'required',
                app(Image::class),
            ],
            'images.*.alt' => [
                'required',
                'string',
                'max:50',
            ],
            'videos' => [
                'nullable',
                'array',
                'max:1',
            ],
            'videos.*.video' => [
                'required',
                'json',
            ],
            'videos.*.image' => [
                'nullable',
                app(Image::class),
            ],
            'videos.*.title' => [
                'nullable',
                'string',
                'max:50',
            ],
            'videos.*.description' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($options = $this->input('options')) {
            foreach ($options as &$option) {
                if (is_null($option['images'])) {
                    $option['images'] = json_encode([]);
                }
            }
        }

        $this->merge(['options' => $options]);

        $id = $this->input('id') ?? request()->route('id');

        if ($this->isEmptyString('seo')) {
            $this->getInputSource()->remove('seo');
        }

        if (is_null($id)) {
            if ($this->isEmptyString('search_url')) {
                $this->getInputSource()->remove('search_url');
            }

            return;
        }

        if ($this->isNotFilled([
            'seo.0.title',
            'seo.0.description',
            'seo.0.image',
            'seo.0.author',
            'seo.0.robots',
        ])) {
            $this->getInputSource()->remove('seo');
        }
    }
}
