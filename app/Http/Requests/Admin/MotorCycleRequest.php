<?php

namespace App\Http\Requests\Admin;

use App\Enums\ProductType;
use App\Models\MotorCycle;
use App\Models\Option;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MotorCycleRequest extends FormRequest
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
            'chassis_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique(MotorCycle::class)->ignore($id),
            ],
            'engine_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique(MotorCycle::class)->ignore($id),
            ],
            'option' => [
                'required',
                'integer',
                Rule::exists(Option::class, 'id'),
                function ($attribute, $value, $fail) {
                    $option = Option::whereId($value)
                        ->whereHas(
                            'product',
                            function (Builder $query) {
                                /** @var Product $query */
                                return $query->whereType(ProductType::MOTOR_CYCLE);
                            })
                        ->exists();

                    if (! $option) {
                        $fail(trans('validation.exists'));
                    }
                },
            ],
        ];
    }
}
