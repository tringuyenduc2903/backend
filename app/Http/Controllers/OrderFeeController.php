<?php

namespace App\Http\Controllers;

use App\Facades\OrderFee;
use App\Http\Requests\OrderRequest;

class OrderFeeController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(OrderRequest $request): array
    {
        $fee = OrderFee::getFee(
            $request->validated('options'),
            $request->validated('shipping_method'),
            $request->validated('address_id')
        );

        $fee['items_preview'] = array_map(
            function (array $item): array {
                $item['price_preview'] = price_preview($item['price']);
                $item['value_added_tax_preview'] = percent_preview($item['value_added_tax']);
                $item['make_money_preview'] = price_preview($item['make_money']);

                unset(
                    $item['price'],
                    $item['value_added_tax'],
                    $item['make_money'],
                );

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
