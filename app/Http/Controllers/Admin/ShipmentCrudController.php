<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EmployeePermission;
use App\Enums\OrderStatus;
use App\Http\Controllers\Admin\Operations\CreateGhnOrderOperation;
use App\Models\Order;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class OrderCrudController
 *
 * @property-read CrudPanel $crud
 */
class ShipmentCrudController extends CrudController
{
    use CreateGhnOrderOperation;
    use ListOperation;
    use ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(Order::class);
        CRUD::setRoute(route('shipments.index'));
        CRUD::setEntityNameStrings(trans('Shipment'), trans('Shipments'));

        CRUD::operation(
            'list',
            fn () => CRUD::addButton('line', 'show', 'view', 'crud.buttons.review.show', 'beginning'));

        CRUD::addClause('whereIn', 'status', [OrderStatus::TO_SHIP, OrderStatus::TO_RECEIVE]);

        deny_access(EmployeePermission::ORDER_CRUD);
    }

    /**
     * Define what happens when the Show operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     */
    protected function setupShowOperation(): void
    {
        app(OrderCrudController::class)->setupShowOperation(
            $this->crud->entity_name
        );
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     *
     * @return void
     */
    protected function setupListOperation()
    {
        app(OrderCrudController::class)->setupListOperation();
    }
}
