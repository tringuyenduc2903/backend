<?php

namespace App\Http\Requests;

use App\Enums\OrderMotorcycleLicensePlateRegistration;
use App\Enums\OrderMotorcycleRegistration;
use App\Enums\OrderPaymentMethod;
use App\Models\Address;
use App\Models\Identification;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderMotorcycleRequest extends FormRequest
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
            'address_id' => [
                'required',
                'integer',
                Rule::exists(Address::class, 'id')
                    ->where('customer_id', fortify_user()->id),
            ],
            'identification_id' => [
                'required',
                'integer',
                Rule::exists(Identification::class, 'id')
                    ->where('customer_id', fortify_user()->id),
            ],
            'note' => [
                'nullable',
                'string',
                'max:255',
            ],
            'cancel_url' => [
                'required_if:payment_method,'.OrderPaymentMethod::BANK_TRANSFER,
                'string',
                'url',
            ],
            'return_url' => [
                'required_if:payment_method,'.OrderPaymentMethod::BANK_TRANSFER,
                'string',
                'url',
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
