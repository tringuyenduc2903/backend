<?php

namespace App\Http\Requests;

use App\Models\Customer;
use App\Models\Option;
use App\Models\Review;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $id = request()->route('review');

        $create = [
            'option_id' => [
                'required',
                'integer',
                Rule::exists(Option::class, 'id'),
                Rule::unique(Review::class, 'parent_id')
                    ->where('parent_type', Option::class)
                    ->where('reviewable_id', request()->user()->id)
                    ->where('reviewable_type', Customer::class),
            ],
        ];

        $update = [
            'content' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($id) {
                    if (! $id) {
                        return;
                    }

                    if (
                        ! Review::whereTime('updated_at', '<=', now()->subDays(7))
                            ->findOrFail($id)
                            ->exists()
                    ) {
                        $fail(trans('validation.custom.max.time'));
                    }
                },
            ],
            'rate' => [
                'required',
                'integer',
                'between:1,5',
            ],
            'images' => [
                'nullable',
                'array',
                'max:5',
            ],
            'images.*' => [
                'required',
                'string',
                'max:255',
            ],
        ];

        return $id
            ? $update
            : array_merge($create, $update);
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'option_id.unique' => trans('validation.custom.max.review'),
        ];
    }
}
