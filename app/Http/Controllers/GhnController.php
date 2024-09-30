<?php

namespace App\Http\Controllers;

use App\Enums\GhnOrderStatus;
use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GhnController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $order = Order::whereShippingCode($request->OrderCode)->firstOrFail();

        $order->shipments()->create([
            'name' => $request->Status,
            'description' => $request->Description,
            'reason' => $request->Reason,
        ]);

        match ($request->Status) {
            GhnOrderStatus::PICKED => $order->update([
                'status' => OrderStatus::TO_RECEIVE,
            ]),
            GhnOrderStatus::DELIVERED => $order->update([
                'status' => OrderStatus::COMPLETED,
            ]),
            GhnOrderStatus::CANCEL => $order->update([
                'status' => OrderStatus::CANCELLED,
            ]),
            default => null,
        };

        return response()->json('', 201);
    }
}
