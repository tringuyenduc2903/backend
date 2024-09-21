<?php

namespace App\Http\Requests\Admin;

use App\Enums\OptionStatus;
use App\Enums\OrderShippingType;
use App\Enums\OrderTransactionType;
use App\Enums\ProductType;
use App\Models\Address;
use App\Models\Customer;
use App\Models\Option;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderRequest extends FormRequest
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
            'options' => [
                'required',
                'array',
            ],
            'options.*.option' => [
                'required',
                'integer',
                Rule::exists(Option::class, 'id')
                    ->where('status', OptionStatus::IN_STOCK),
                function ($attribute, $value, $fail) {
                    if (! $value) {
                        return;
                    }

                    $option = Option::find($value);

                    if (! $option) {
                        return;
                    } elseif (
                        ! $option->product->published ||
                        $option->product->type === ProductType::MOTOR_CYCLE

                    ) {
                        $fail(trans('validation.exists'));
                    }
                },
            ],
            'options.*.amount' => [
                'required',
                'integer',
                'between:1,65535',
                function ($attribute, $value, $fail) {
                    if ($value < 1) {
                        return;
                    }

                    $field = str_replace('.amount', '.option', $attribute);

                    if ($this->isNotFilled($field)) {
                        return;
                    }

                    $option = Option::whereStatus(OptionStatus::IN_STOCK)
                        ->whereHas(
                            'product',
                            function (Builder $query) {
                                /** @var Product $query */
                                return $query
                                    ->wherePublished(true)
                                    ->whereNot('type', ProductType::MOTOR_CYCLE);
                            }
                        )
                        ->find(request($field));

                    if (! $option) {
                        return;
                    }

                    if ($value > 5) {
                        $fail(trans('validation.max.numeric', [
                            'max' => 5,
                        ]));
                    } elseif ($value > $option->quantity) {
                        $fail(trans('validation.max.numeric', [
                            'max' => $option->quantity,
                        ]));
                    }
                },
            ],
            'shipping_type' => [
                'required',
                'integer',
                Rule::in(OrderShippingType::keys()),
            ],
            'transaction_type' => [
                'required',
                'integer',
                Rule::in(OrderTransactionType::keys()),
            ],
            'customer' => [
                'required',
                'integer',
                Rule::exists(Customer::class, 'id'),
            ],
            'address' => [
                'required',
                'integer',
                Rule::exists(Address::class, 'id')
                    ->where('customer_id', $this->input('customer')),
            ],
            'note' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }
}
