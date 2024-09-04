<?php

namespace App\Http\Requests\Admin;

use App\Actions\Fortify\PasswordValidationRules;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeStoreCrudRequest extends FormRequest
{
    use PasswordValidationRules;

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
                'max:50',
            ],
            'email' => [
                'required',
                'string',
                'max:100',
                Rule::unique(Employee::class),
            ],
            'password' => $this->passwordRules(),
            'branch' => [
                'required',
                'integer',
                Rule::exists(Branch::class, 'id'),
            ],
            'roles' => [
                'required',
                'array',
                Rule::exists(Role::class, 'id'),
            ],
        ];
    }
}
