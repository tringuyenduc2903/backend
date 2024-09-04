<?php

namespace App\Http\Requests\Admin;

use App\Enums\CustomerGender;
use App\Models\Customer;
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
        ];
    }
}
