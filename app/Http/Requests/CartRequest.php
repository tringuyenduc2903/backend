<?php

namespace App\Http\Requests;

use App\Enums\OptionStatus;
use App\Enums\ProductType;
use App\Models\Cart;
use App\Models\Option;
use App\Models\Product;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CartRequest extends FormRequest
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
        $id = request()->route('cart');

        $option_id = [
            'option_id' => [
                'required',
                'integer',
                Rule::exists(Option::class, 'id'),
                function ($attribute, $value, $fail) {
                    if (! $value) {
                        return;
                    }

                    $option = Option::find($value);

                    if (! $option) {
                        return;
                    } elseif (! $option->product->getRawOriginal('published')) {
                        $fail(trans('validation.custom.product.published'));
                    } elseif ($option->getRawOriginal('status') === OptionStatus::OUT_OF_STOCK) {
                        $fail(trans('validation.custom.product.out_of_stock'));
                    } elseif ($option->product->getRawOriginal('type') === ProductType::MOTOR_CYCLE) {
                        $fail(trans('validation.custom.product.motor_cycle'));
                    }
                },
                Rule::unique(Cart::class)->where(
                    'customer_id',
                    request()->user()->id
                ),
            ],
        ];

        $amount = [
            'amount' => [
                'required',
                'integer',
                'between:1,65535',
                function ($attribute, $value, $fail) {
                    if ($value < 1) {
                        return;
                    }

                    if (request()->isNotFilled('option_id')) {
                        return;
                    }

                    $option = Option::whereId(request('option_id'))
                        ->whereStatus(OptionStatus::IN_STOCK)
                        ->whereHas(
                            'product',
                            function (Builder $query) {
                                /** @var Product $query */
                                return $query
                                    ->wherePublished(true)
                                    ->whereNot('type', ProductType::MOTOR_CYCLE);
                            }
                        )
                        ->first();

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
        ];

        return $id
            ? $amount
            : array_merge($option_id, $amount);
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'option_id.unique' => trans('The product is already in :list', [
                'list' => trans('Cart'),
            ]),
        ];
    }
}
