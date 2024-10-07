<?php

namespace App\Http\Controllers;

use App\Facades\OrderMotorcycleFee;
use App\Http\Requests\OrderMotorcycleRequest;

class FeeMotorcycleController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(OrderMotorcycleRequest $request): array
    {
        $fee = OrderMotorcycleFee::getFee(
            $request->validated('option_id'),
            $request->validated('motorcycle_registration_support'),
            $request->validated('registration_option'),
            $request->validated('license_plate_registration_option')
        );

        $fee['item']['price_preview'] = price_preview($fee['item']['price']);
        $fee['item']['value_added_tax_preview'] = percent_preview($fee['item']['value_added_tax']);

        $fee['motorcycle_registration_support_fee_preview'] = price_preview($fee['motorcycle_registration_support_fee']);
        $fee['registration_fee_preview'] = price_preview($fee['registration_fee']);
        $fee['license_plate_registration_fee_preview'] = price_preview($fee['license_plate_registration_fee']);
        $fee['price_preview'] = price_preview($fee['price']);
        $fee['tax_preview'] = price_preview($fee['tax']);
        $fee['handling_fee_preview'] = price_preview($fee['handling_fee']);
        $fee['total_preview'] = price_preview($fee['total']);

        unset(
            $fee['item']['price'],
            $fee['item']['value_added_tax'],
            $fee['motorcycle_registration_support_fee'],
            $fee['registration_fee'],
            $fee['license_plate_registration_fee'],
            $fee['price'],
            $fee['tax'],
            $fee['handling_fee'],
            $fee['total'],
        );

        return $fee;
    }
}
