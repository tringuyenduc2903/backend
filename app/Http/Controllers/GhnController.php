<?php

namespace App\Http\Controllers;

use App\Enums\GhnOrderStatusEnum;
use App\Enums\OrderStatus;
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
        $order = Order::whereShippingCode($request->validated('OrderCode'))->firstOrFail();

        $order->shipments()->create([
            'name' => $request->validated('Status'),
            'description' => $request->validated('Description'),
            'reason' => $request->validated('Reason'),
        ]);

        if ($request->validated('Status') === GhnOrderStatusEnum::PICKED) {
            $order->update([
                'status' => OrderStatus::TO_RECEIVE,
            ]);
        }

        return response()->json('');
    }
}
