<?php

namespace App\Http\Controllers;

use App\Http\Requests\GhnRequest;
use App\Models\Order;

class GhnController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(GhnRequest $request)
    {
        Order::whereShippingCode($request->validated('OrderCode'))
            ->firstOrFail()
            ->shipments()
            ->create([
                'name' => $request->validated('Type'),
                'description' => $request->validated('Description'),
                'status' => $request->validated('Status'),
            ]);
    }
}
