<?php

namespace App\Http\Controllers;

use App\Actions\OrderFee;
use App\Http\Requests\OrderRequest;

class FeeController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(OrderRequest $request): array
    {
        $fee = app(OrderFee::class, [
            'options' => $request->validated('options'),
            'shipping_method' => $request->validated('shipping_method'),
            'address_id' => $request->validated('address_id'),
        ])->result;

        $fee['items_preview'] = array_map(
            function (array $item): array {
                $item['price_preview'] = price_preview($item['price']);
                unset($item['price']);

                return $item;
            },
            $fee['items']
        );

        $fee['price_preview'] = price_preview($fee['price']);
        $fee['tax_preview'] = price_preview($fee['tax']);
        $fee['shipping_fee_preview'] = price_preview($fee['shipping_fee']);
        $fee['handling_fee_preview'] = price_preview($fee['handling_fee']);
        $fee['total_preview'] = price_preview($fee['total']);

        unset(
            $fee['items'],
            $fee['price'],
            $fee['tax'],
            $fee['shipping_fee'],
            $fee['handling_fee'],
            $fee['total'],
        );

        return $fee;
    }
}
