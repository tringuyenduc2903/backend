<?php

namespace App\Http\Controllers\Admin\Operations;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderMotorcycle;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

trait ProductHandoverOperation
{
    /**
     * Define which routes are needed for this operation.
     *
     * @param  string  $segment  Name of the current entity (singular). Used as first URL segment.
     * @param  string  $routeName  Prefix of the route name.
     * @param  string  $controller  Name of the current CrudController.
     */
    protected function setupProductHandoverRoutes(string $segment, string $routeName, string $controller)
    {
        Route::post($segment.'/{id}/product-handover', [
            'as' => $routeName.'.productHandover',
            'uses' => $controller.'@productHandover',
            'operation' => 'product_handover',
        ]);
    }

    /**
     * Add the default settings, buttons, etc that this operation needs.
     */
    protected function setupProductHandoverDefaults()
    {
        CRUD::setAccessCondition(
            'product_handover',
            fn (Order|OrderMotorcycle $entry): bool => $entry->canProductHandover()
        );

        CRUD::operation(
            ['list', 'show'],
            fn () => CRUD::addButton('line', 'product_handover', 'view', 'crud.buttons.shipment.product_handover', 'end'));
    }

    protected function productHandover(string $id): JsonResponse
    {
        /** @var Order $model */
        $model = CRUD::getModel();

        $order = $model->findOrFail($id);

        if (! $order->canProductHandover()) {
            return response()->json([
                'title' => trans('Failed'),
                'description' => trans('Products cannot be delivered with this Order.'),
            ], 403);
        }

        $order
            ->forceFill([
                'status' => OrderStatus::COMPLETED,
            ])
            ->save();

        return response()->json([
            'title' => trans('Successfully'),
            'description' => trans('Handover of products to order Id #:number successfully!', [
                'number' => $order->id,
            ]),
        ]);
    }
}
