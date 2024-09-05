<?php

namespace App\Http\Requests\Admin;

use App\Enums\CustomerAddress;
use App\Enums\CustomerGender;
use App\Enums\CustomerIdentification;
use App\Models\Customer;
use App\Models\District;
use App\Models\Identification;
use App\Models\Province;
use App\Models\Ward;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class CustomerRequest extends FormRequest
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
        $timezone = isset($id)
            ? timezone_identifiers_list()[Customer::findOrFail($id)->timezone]
            : config('app.timezone');
        $default_address = false;
        $default_identification = false;

        return [
            'name' => [
                'required',
                'string',
                'max:50',
            ],
            'email' => [
                'required',
                'string',
                'email:rfc,dns',
                'max:100',
                Rule::unique(Customer::class)->ignore($id),
            ],
            'phone_number' => [
                'nullable',
                'string',
                'phone:VN',
                Rule::unique(Customer::class)->ignore($id),
            ],
            'birthday' => [
                'nullable',
                'date',
                'before_or_equal:'.Carbon::now($timezone)->subYears(16),
                'after_or_equal:'.Carbon::now($timezone)->subYears(100),
            ],
            'gender' => [
                'nullable',
                'integer',
                Rule::in(CustomerGender::keys()),
            ],
            'timezone' => [
                'required',
                'integer',
                Rule::in(array_keys(timezone_identifiers_list())),
            ],
            'addresses' => [
                'nullable',
                'array',
                'max:5',
                Rule::when(
                    $this->input('addresses') &&
                    ! in_array(
                        true,
                        array_column($this->input('addresses'), 'default')
                    ),
                    'accepted'
                ),
            ],
            'addresses.*.default' => [
                'required',
                'boolean',
                function ($attribute, $value, $fail) use (&$default_address) {
                    if ($value) {
                        if ($default_address) {
                            $fail(trans('validation.custom.default.unique', [
                                'attribute' => trans('Addresses'),
                            ]));
                        }

                        $default_address = true;
                    }
                },
            ],
            'addresses.*.type' => [
                'required',
                'integer',
                Rule::in(CustomerAddress::keys()),
            ],
            'addresses.*.customer_name' => [
                'required',
                'string',
                'max:50',
            ],
            'addresses.*.customer_phone_number' => [
                'required',
                'string',
                'phone:VN',
            ],
            'addresses.*.country' => [
                'required',
                'string',
                'max:100',
            ],
            'addresses.*.province' => [
                'required',
                'integer',
                Rule::exists(Province::class, 'id'),
            ],
            'addresses.*.district' => [
                'required',
                'integer',
                Rule::exists(District::class, 'id')
                    ->where('province_id', $this->input('addresses.*.province')),
            ],
            'addresses.*.ward' => [
                'nullable',
                Rule::requiredIf(
                    Ward::whereDistrictId($this->input('addresses.*.district'))->exists()
                ),
                'integer',
                Rule::exists(Ward::class, 'id')
                    ->where('district_id', $this->input('addresses.*.district')),
            ],
            'addresses.*.address_detail' => [
                'required',
                'string',
                'max:255',
            ],
            'identifications' => [
                'nullable',
                'array',
                'max:5',
                Rule::when(
                    $this->input('identifications') &&
                    ! in_array(
                        true,
                        array_column($this->input('identifications'), 'default')
                    ),
                    'accepted'
                ),
            ],
            'identifications.*.default' => [
                'required',
                'boolean',
                function ($attribute, $value, $fail) use (&$default_identification) {
                    if ($value) {
                        if ($default_identification) {
                            $fail(trans('validation.custom.default.unique', [
                                'attribute' => trans('Identifications'),
                            ]));
                        }

                        $default_identification = true;
                    }
                },
            ],
            'identifications.*.type' => [
                'required',
                'integer',
                Rule::in(CustomerIdentification::keys()),
            ],
            'identifications.*.number' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $strlen = strlen($value);

                    $type = request(
                        str_replace('.number', '.type', $attribute)
                    );

                    switch ((int) $type) {
                        case CustomerIdentification::IDENTITY_CARD:
                            if (! in_array($strlen, [9, 12])) {
                                $fail(trans('validation.custom.size.strings', [
                                    'size1' => 9,
                                    'size2' => 12,
                                ]));
                            }
                            break;
                        case CustomerIdentification::CITIZEN_IDENTIFICATION_CARD:
                            if ($strlen !== 12) {
                                $fail(trans('validation.size.string', [
                                    'size' => 12,
                                ]));
                            }
                            break;
                    }
                },
                'max:100',
                Rule::unique(Identification::class)->ignore($this->input('id'), 'customer_id'),
            ],
            'identifications.*.issued_name' => [
                'required',
                'string',
                'max:255',
            ],
            'identifications.*.issuance_date' => [
                'required',
                'date',
            ],
            'identifications.*.expiry_date' => [
                'required',
                'date',
                'after:identifications.*.issuance_date',
                'after:'.Carbon::now($timezone),
            ],
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     */
    public function messages(): array
    {
        return [
            'addresses.accepted' => trans('validation.custom.default.accepted'),
            'identifications.accepted' => trans('validation.custom.default.accepted'),
        ];
    }
}
