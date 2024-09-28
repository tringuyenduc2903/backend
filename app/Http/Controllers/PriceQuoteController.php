<?php

namespace App\Http\Controllers;

use App\Actions\OrderPrice;
use App\Enums\OrderShippingMethod;
use App\Http\Requests\OrderRequest;
use Exception;
use Illuminate\Validation\ValidationException;

class PriceQuoteController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(OrderRequest $request): array
    {
        try {
            $price_quote = app(OrderPrice::class, [
                'options' => $request->validated('options'),
                'shipping_method' => $request->validated('shipping_method'),
                'address_id' => $request->validated('address_id'),
            ])->getPriceQuote();

            $price_quote['items_preview'] = array_map(
                function (array $item): array {
                    $item['price_preview'] = price_preview($item['price']);
                    unset($item['price']);

                    return $item;
                },
                $price_quote['items']
            );

            $price_quote['price_preview'] = price_preview($price_quote['price']);
            $price_quote['tax_preview'] = price_preview($price_quote['tax']);
            $price_quote['shipping_fee_preview'] = price_preview($price_quote['shipping_fee']);
            $price_quote['handling_fee_preview'] = price_preview($price_quote['handling_fee']);
            $price_quote['total_preview'] = price_preview($price_quote['total']);

            unset(
                $price_quote['items'],
                $price_quote['price'],
                $price_quote['tax'],
                $price_quote['shipping_fee'],
                $price_quote['handling_fee'],
                $price_quote['total'],
            );

            return $price_quote;
        } catch (Exception) {
            throw ValidationException::withMessages([
                'shipping_method' => trans('Shipping method :name is not available for this order', [
                    'name' => OrderShippingMethod::valueForKey(request('shipping_method')),
                ]),
            ]);
        }
    }
}
