<?php

namespace App\Http\Requests\Admin;

use App\Enums\OrderMotorcycleLicensePlateRegistration;
use App\Enums\OrderMotorcycleRegistration;
use App\Enums\OrderPaymentMethod;
use App\Models\Address;
use App\Models\Customer;
use App\Models\Identification;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderMotorcycleRequest extends FormRequest
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
            'option' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    if (! get_product($value, false, true)) {
                        $fail(trans('validation.exists'));
                    }
                },
            ],
            'motorcycle_registration_support' => [
                'required',
                'boolean',
            ],
            'registration_option' => [
                'required_if_accepted:motorcycle_registration_support',
                'nullable',
                'integer',
                Rule::in(OrderMotorcycleRegistration::keys()),
            ],
            'license_plate_registration_option' => [
                'required_if_accepted:motorcycle_registration_support',
                'nullable',
                'integer',
                Rule::in(OrderMotorcycleLicensePlateRegistration::keys()),
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
            'identification' => [
                'required',
                'integer',
                Rule::exists(Identification::class, 'id')
                    ->where('customer_id', $this->input('customer')),
            ],
            'note' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     */
    public function messages(): array
    {
        return [
            'registration_option.required_if_accepted' => trans('validation.required'),
            'license_plate_registration_option.required_if_accepted' => trans('validation.required'),
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if (! $this->input('motorcycle_registration_support')) {
            $this->merge([
                'registration_option' => null,
                'license_plate_registration_option' => null,
            ]);
        }
    }
}
