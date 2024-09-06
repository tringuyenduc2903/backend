<?php

namespace App\Http\Requests\Admin;

use App\Enums\ProductType;
use App\Enums\ProductVisibility;
use App\Models\Product;
use App\Rules\Image;
use Illuminate\Foundation\Http\FormRequest;
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
}
