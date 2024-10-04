<?php

namespace App\Http\Controllers\Admin\Operations;

use App\Facades\GhnApi;
use App\Models\Order;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

trait CreateGhnOrderOperation
{
    /**
     * Define which routes are needed for this operation.
     *
     * @param  string  $segment  Name of the current entity (singular). Used as first URL segment.
     * @param  string  $routeName  Prefix of the route name.
     * @param  string  $controller  Name of the current CrudController.
     */
    protected function setupCreateGhnOrderRoutes(string $segment, string $routeName, string $controller)
    {
        Route::delete($segment.'/{id}/create-ghn-order', [
            'as' => $routeName.'.createGhnOrder',
            'uses' => $controller.'@createGhnOrder',
            'operation' => 'create_ghn_order',
        ]);
    }

    /**
     * Add the default settings, buttons, etc that this operation needs.
     */
    protected function setupCreateGhnOrderDefaults()
    {
        CRUD::setAccessCondition(
            'create_ghn_order',
            fn (Order $entry): bool => $entry->canCreateGhnOrder()
        );

        CRUD::operation(
            ['list', 'show'],
            fn () => CRUD::addButton('line', 'create_ghn_order', 'view', 'crud.buttons.shipment.create_ghn_order', 'end'));
    }

    protected function createGhnOrder(string $id): JsonResponse
    {
        /** @var Order $model */
        $model = CRUD::getModel();

        $order = $model->findOrFail($id);

        if (! $order->canCreateGhnOrder()) {
            return response()->json([
                'title' => trans('Failed'),
                'description' => trans('Cannot create GHN order with this Order.', [
                    'name' => $order->status_preview,
                ]),
            ], 403);
        }

        try {
            $response = GhnApi::createOrder($order);

            $order
                ->forceFill([
                    'shipping_code' => $response['order_code'],
                ])
                ->save();

            return response()->json([
                'title' => trans('Successfully'),
                'description' => trans('Created GHN order for order Id #:number of goods successfully!', [
                    'number' => $order->id,
                ]),
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'title' => trans('Failed'),
                'description' => $exception->getMessage(),
            ], 500);
        }
    }
}
