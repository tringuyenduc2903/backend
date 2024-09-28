<?php

namespace App\Http\Requests;

use App\Models\Cart;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CartRequest extends FormRequest
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
        $id = request()->route('cart');

        $create = [
            'option_id' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    if (! get_product($value)) {
                        $fail(trans('validation.exists'));
                    }
                },
                Rule::unique(Cart::class)->where(
                    'customer_id',
                    fortify_user()->id
                ),
            ],
        ];

        $update = [
            'amount' => [
                'required',
                'integer',
                'between:1,5',
                function ($attribute, $value, $fail) {
                    if ($value < 1 || $value > 5) {
                        return;
                    }

                    if (! $option = get_product(
                        $this->input('option_id')
                    )) {
                        return;
                    }

                    if ($value > $option->quantity) {
                        $fail(trans('validation.max.numeric', [
                            'max' => $option->quantity,
                        ]));
                    }
                },
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
            'option_id.unique' => trans('The product is already in :list', [
                'list' => trans('Cart'),
            ]),
        ];
    }
}
