<?php

namespace App\Http\Requests\Admin;

use App\Enums\ProductType;
use App\Enums\ProductVisibility;
use App\Models\Product;
use App\Rules\Image;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProductUpdateRequest extends FormRequest
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
                'required',
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
        if ($this->isEmptyString('seo')) {
            $this->getInputSource()->remove('seo');
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
