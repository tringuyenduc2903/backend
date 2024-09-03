<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
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

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(config('permission.table_names.roles'))->ignore($id),
            ],
            'permissions' => [
                'required',
                'array',
                Rule::exists(config('permission.table_names.permissions'), 'id'),
            ],
        ];
    }
}
