<?php

namespace App\Http\Requests\Admin\Operations;

use App\Models\Employee;
use App\Models\MotorCycle;
use App\Models\OrderMotorcycle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MotorcycleHandoverRequest extends FormRequest
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

        /** @var Employee $employee */
        $employee = backpack_user();

        return [
            'motor_cycle' => [
                'required',
                'integer',
                Rule::exists(MotorCycle::class, 'id')
                    ->where('branch_id', $employee->branch->id)
                    ->where('option_id', OrderMotorcycle::findOrFail($id)->option->id),
            ],
        ];
    }
}
