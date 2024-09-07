<?php

namespace App\Http\Requests\Admin;

use App\Enums\OptionStatus;
use App\Enums\OptionType;
use App\Enums\ProductType;
use App\Enums\ProductVisibility;
use App\Models\Option;
use App\Models\Product;
use App\Rules\Image;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProductStoreRequest extends FormRequest
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
        $type = $this->input('type');

        return [
            'enabled' => [
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
                'max:30',
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
                'sometimes',
                'string',
                'max:255',
                Rule::unique(Product::class),
            ],
            'seo' => [
                'nullable',
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
                    $id = request(
                        str_replace('.sku', '.id', $attribute)
                    );

                    $validator = Validator::make([
                        'sku' => $value,
                    ], [
                        'sku' => Rule::unique(Option::class)->ignore($id),
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
                'required',
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
                            'max:30',
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
        if ($this->isEmptyString('search_url')) {
            $this->getInputSource()->remove('search_url');
        }
    }
}
