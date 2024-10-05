<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EmployeePermission;
use App\Enums\OrderStatus;
use App\Http\Controllers\Admin\Operations\AddTransactionOperation;
use App\Models\Order;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class TransactionCrudController
 *
 * @property-read CrudPanel $crud
 */
class TransactionCrudController extends CrudController
{
    use AddTransactionOperation;
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
        CRUD::setRoute(route('transactions.index'));
        CRUD::setEntityNameStrings(trans('Transaction'), trans('Transactions'));

        CRUD::addClause('whereIn', 'status', [OrderStatus::TO_PAY]);

        deny_access(EmployeePermission::TRANSACTION_CRUD);
    }

    /**
     * Define what happens when the Show operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     */
    protected function setupShowOperation(): void
    {
        app(OrderCrudController::class)->setupShowOperation();
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

        CRUD::removeFilter('status');
    }
}
