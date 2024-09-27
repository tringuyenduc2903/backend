<?php

namespace App\Http\Requests;

use App\Enums\OrderPaymentMethod;
use App\Enums\OrderShippingMethod;
use App\Models\Address;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderRequest extends FormRequest
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
            'options' => [
                'required',
                'array',
            ],
            'options.*.option_id' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    if (!get_product($value)) {
                        $fail(trans('validation.exists'));
                    }
                },
            ],
            'options.*.amount' => [
                'required',
                'integer',
                'between:1,5',
                function ($attribute, $value, $fail) {
                    if ($value < 1 || $value > 5) {
                        return;
                    }

                    if (!$option = get_product(
                        request('options.*.option_id')
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
            'shipping_method' => [
                'required',
                'integer',
                Rule::in(OrderShippingMethod::keys()),
            ],
            'payment_method' => [
                'required',
                'integer',
                Rule::in(OrderPaymentMethod::keys()),
            ],
            'note' => [
                'nullable',
                'string',
                'max:255',
            ],
            'address_id' => [
                'required',
                'integer',
                Rule::exists(Address::class, 'id')
                    ->where('customer_id', fortify_user()->id),
            ],
        ];
    }
}
