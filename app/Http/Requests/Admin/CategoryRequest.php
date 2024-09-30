<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use App\Rules\Image;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:20',
            ],
            'description' => [
                'nullable',
                'string',
                'max:4294967295',
            ],
            'image' => [
                'sometimes',
                app(Image::class),
            ],
            'alt' => [
                'nullable',
                'required_with:image',
                'string',
                'max:50',
            ],
            'search_url' => [
                $id ? 'required' : 'sometimes',
                'string',
                'max:255',
                Rule::unique(Category::class)->ignore($id),
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
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $id = $this->input('id') ?? request()->route('id');

        if ($this->isEmptyString('seo')) {
            $this->getInputSource()->remove('seo');
        }

        if (is_null($id)) {
            if ($this->isEmptyString('search_url')) {
                $this->getInputSource()->remove('search_url');
            }
        } elseif ($this->isNotFilled([
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
