<?php

namespace App\Http\Requests\Admin;

use App\Enums\OrderPaymentMethod;
use App\Enums\OrderShippingMethod;
use App\Models\Address;
use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderRequest extends FormRequest
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
        return [
            'options' => [
                'required',
                'array',
                'max:20',
            ],
            'options.*.option' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    if (! get_product($value)) {
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

                    if (! $option = get_product(
                        $this->input(str_replace('.amount', '.option', $attribute))
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
            'customer' => [
                'required',
                'integer',
                Rule::exists(Customer::class, 'id'),
            ],
            'address' => [
                'required',
                'integer',
                Rule::exists(Address::class, 'id')
                    ->where('customer_id', $this->input('customer')),
            ],
            'note' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }
}
