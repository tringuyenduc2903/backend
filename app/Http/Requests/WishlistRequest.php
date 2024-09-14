<?php

namespace App\Http\Requests;

use App\Enums\OptionStatus;
use App\Models\Option;
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
                Rule::exists(Option::class, 'id'),
                function ($attribute, $value, $fail) {
                    if (! $value) {
                        return;
                    }

                    $option = Option::find($value);

                    if (! $option) {
                        return;
                    } elseif (! $option->product->getRawOriginal('published')) {
                        $fail(trans('validation.custom.product.published'));
                    } elseif ($option->getRawOriginal('status') === OptionStatus::OUT_OF_STOCK) {
                        $fail(trans('validation.custom.product.out_of_stock'));
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
