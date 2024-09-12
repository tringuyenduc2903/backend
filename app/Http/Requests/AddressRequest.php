<?php

namespace App\Http\Requests;

use App\Enums\CustomerAddress;
use App\Models\District;
use App\Models\Province;
use App\Models\Ward;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddressRequest extends FormRequest
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
        $id = request()->route('address');

        return [
            'default' => [
                'required',
                'boolean',
                Rule::when(
                    $id
                        ? request()->user()->addresses()->findOrFail($id)->default
                        : ! request()->user()->addresses()->whereDefault(true)->exists(),
                    'accepted'
                ),
                function ($attribute, $value, $fail) use ($id) {
                    if ($id) {
                        return;
                    }

                    if (request()->user()->addresses()->count() > 4) {
                        $fail(trans('validation.custom.max.entity', [
                            'attribute' => trans('Address'),
                        ]));
                    }
                },
            ],
            'type' => [
                'required',
                'integer',
                Rule::in(CustomerAddress::keys()),
            ],
            'customer_name' => [
                'required',
                'string',
                'max:50',
            ],
            'customer_phone_number' => [
                'required',
                'string',
                'phone:VN',
            ],
            'country' => [
                'required',
                'string',
                'max:100',
            ],
            'province_id' => [
                'required',
                'integer',
                Rule::exists(Province::class, 'id'),
            ],
            'district_id' => [
                'required',
                'integer',
                Rule::exists(District::class, 'id')
                    ->where('province_id', $this->input('province_id')),
            ],
            'ward_id' => [
                'nullable',
                Rule::requiredIf(
                    Ward::whereDistrictId($this->input('district_id'))->exists()
                ),
                'integer',
                Rule::exists(Ward::class, 'id')
                    ->where('district_id', $this->input('district_id')),
            ],
            'address_detail' => [
                'required',
                'string',
                'max:255',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'default.accepted' => trans('validation.custom.default.accepted', [
                'attribute' => trans('Address'),
            ]),
        ];
    }
}
