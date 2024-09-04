<?php

namespace App\Http\Requests\Admin;

use App\Models\Branch;
use App\Models\District;
use App\Models\Province;
use App\Models\Ward;
use App\Rules\Image;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BranchRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'phone_number' => [
                'nullable',
                'phone:VN',
                Rule::unique(Branch::class)->ignore($this->input('id')),
            ],
            'image' => [
                'sometimes',
                app(Image::class),
            ],
            'alt' => [
                'nullable',
                'required_with:image',
                'string',
                'max:50',
            ],
            'province' => [
                'required',
                'integer',
                Rule::exists(Province::class, 'id'),
            ],
            'district' => [
                'required',
                'integer',
                Rule::exists(District::class, 'id')
                    ->where('province_id', request('province')),
            ],
            'ward' => [
                'nullable',
                Rule::requiredIf(
                    Ward::whereDistrictId(request('district'))->exists()
                ),
                'integer',
                Rule::exists(Ward::class, 'id')
                    ->where('district_id', request('district')),
            ],
            'address_detail' => [
                'required',
                'string',
                'max:255',
            ],
        ];
    }
}
