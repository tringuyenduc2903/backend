<?php

namespace App\Http\Controllers\Admin\Operations;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

trait CancelOrderOperation
{
    /**
     * Define which routes are needed for this operation.
     *
     * @param  string  $segment  Name of the current entity (singular). Used as first URL segment.
     * @param  string  $routeName  Prefix of the route name.
     * @param  string  $controller  Name of the current CrudController.
     */
    protected function setupCancelOrderRoutes(string $segment, string $routeName, string $controller)
    {
        Route::delete($segment.'/{id}/cancel-order', [
            'as' => $routeName.'.cancel',
            'uses' => $controller.'@cancel',
            'operation' => 'cancel_order',
        ]);
    }

    protected function cancel(string $id): JsonResponse
    {
        $order = Order::findOrFail($id);

        if ($order->status != OrderStatus::TO_PAY) {
            return response()->json([
                'title' => trans('Failure'),
                'description' => trans('Cancel order Id #:number failed!', [
                    'number' => $order->id,
                ]),
            ], 403);
        }

        $order->update([
            'status' => OrderStatus::CANCELLED,
        ]);

        return response()->json([
            'title' => trans('Success'),
            'description' => trans('Cancellation of order Id #:number successfully!', [
                'number' => $order->id,
            ]),
        ]);
    }
}
