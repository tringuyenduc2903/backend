<?php

namespace App\Http\Controllers;

use App\Http\Requests\GhnRequest;
use App\Models\Order;
use Illuminate\Http\JsonResponse;

class GhnController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(GhnRequest $request): JsonResponse
    {
        Order::whereShippingCode($request->validated('OrderCode'))
            ->firstOrFail()
            ->shipments()
            ->create([
                'name' => $request->validated('Type'),
                'description' => $request->validated('Description'),
                'status' => $request->validated('Status'),
            ]);

        return response()->json('');
    }
}
