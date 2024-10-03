<?php

namespace App\Http\Controllers\Admin\Operations;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderMotorcycle;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
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
            'as' => $routeName.'.cancelOrder',
            'uses' => $controller.'@cancelOrder',
            'operation' => 'cancel_order',
        ]);
    }

    /**
     * Add the default settings, buttons, etc that this operation needs.
     */
    protected function setupCancelOrderDefaults()
    {
        CRUD::setAccessCondition(
            'cancel_order',
            fn (Order|OrderMotorcycle $entry): bool => $entry->canCancel()
        );

        CRUD::operation('create', function () {
            CRUD::loadDefaultOperationSettingsFromConfig();
            CRUD::setupDefaultSaveActions();
        });

        CRUD::operation(
            ['list', 'show'],
            fn () => CRUD::addButton('line', 'cancel_order', 'view', 'crud.buttons.order.cancel_order', 'end'));
    }

    protected function cancelOrder(string $id): JsonResponse
    {
        /** @var Order $model */
        $model = CRUD::getModel();

        $order = $model->findOrFail($id);

        if (! $order->canCancel()) {
            return response()->json([
                'title' => trans('Failed'),
                'description' => trans('Orders with status :name cannot be canceled.', [
                    'name' => $order->status_preview,
                ]),
            ], 403);
        }

        $order->update([
            'status' => OrderStatus::CANCELLED,
        ]);

        return response()->json([
            'title' => trans('Successfully'),
            'description' => trans('Cancellation of order Id #:number successfully!', [
                'number' => $order->id,
            ]),
        ]);
    }
}
