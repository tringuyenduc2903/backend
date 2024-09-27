<?php

namespace App\Http\Requests;

use App\Enums\GhnOrderReasonEnum;
use App\Enums\GhnOrderStatusEnum;
use App\Models\Order;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GhnRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'OrderCode' => [
                'required',
                'string',
                Rule::exists(Order::class, 'shipping_code'),
            ],
            'Status' => [
                'required',
                'string',
                Rule::in(GhnOrderStatusEnum::keys()),
            ],
            'Description' => [
                'required',
                'string',
            ],
            'Reason' => [
                'nullable',
                'string',
                Rule::when(
                    $this->input('Reason'),
                    Rule::in(GhnOrderReasonEnum::keys()),
                ),
            ],
        ];
    }
}
