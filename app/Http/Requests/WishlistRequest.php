<?php

namespace App\Http\Requests;

use App\Models\Wishlist;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WishlistRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return fortify_auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'option_id' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    if (! get_product($value, false)) {
                        $fail(trans('validation.exists'));
                    }
                },
                Rule::unique(Wishlist::class)->where(
                    'customer_id',
                    fortify_user()->id
                ),
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'option_id.unique' => trans('The product is already in :list', [
                'list' => trans('Wishlist'),
            ]),
        ];
    }
}
