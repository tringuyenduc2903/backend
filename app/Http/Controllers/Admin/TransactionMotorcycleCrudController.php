<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EmployeePermission;
use App\Enums\OrderPaymentMethod;
use App\Enums\OrderStatus;
use App\Enums\OrderTransactionStatus;
use App\Http\Controllers\Admin\Operations\AddTransactionOperation;
use App\Models\OrderMotorcycle;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class TransactionMotorcycleCrudController
 *
 * @property-read CrudPanel $crud
 */
class TransactionMotorcycleCrudController extends CrudController
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
        CRUD::setModel(OrderMotorcycle::class);
        CRUD::setRoute(route('transaction-motorcycles.index'));
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
        $this->setupListOperation();

        $code = ' '.current_currency();

        CRUD::addColumn([
            'name' => 'transactions',
            'label' => trans('Transactions'),
            'subfields' => [[
                'name' => 'amount',
                'label' => trans('Amount (Money)'),
                'type' => 'number',
                'suffix' => $code,
            ], [
                'name' => 'status',
                'label' => trans('Status'),
                'type' => 'select2_from_array',
                'options' => OrderTransactionStatus::values(),
            ], [
                'name' => 'reference',
                'label' => trans('Reference'),
            ], [
                'name' => CRUD::getModel()->getCreatedAtColumn(),
                'label' => trans('Created at'),
            ], [
                'name' => CRUD::getModel()->getUpdatedAtColumn(),
                'label' => trans('Updated at'),
            ]],
        ]);
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
        $code = ' '.current_currency();

        CRUD::column('id')
            ->label(trans('Id'));
        CRUD::column('address.customer_name')
            ->label(trans('Name'))
            ->searchLogic(
                fn (Builder $query, array $_, string $search_term): Builder => $query->orWhereHas(
                    'address',
                    fn (Builder $query): Builder => $query->whereLike('customer_name', "%$search_term%")
                )
            );
        CRUD::addColumn([
            'name' => 'address',
            'label' => trans('Phone number'),
            'attribute' => 'customer_phone_number',
            'searchLogic' => fn (Builder $query, array $_, string $search_term): Builder => $query->orWhereHas(
                'address',
                fn (Builder $query): Builder => $query->whereLike(
                    'customer_phone_number',
                    "%$search_term%"
                )
            ),
        ]);
        CRUD::addColumn([
            'name' => 'payment_method',
            'label' => trans('Payment method'),
            'type' => 'select2_from_array',
            'options' => OrderPaymentMethod::values(),
        ]);
        CRUD::addColumn([
            'name' => 'payment_method',
            'label' => trans('Payment method'),
            'type' => 'select2_from_array',
            'options' => OrderPaymentMethod::values(),
        ]);
        CRUD::addColumn([
            'name' => 'total',
            'label' => trans('Total'),
            'type' => 'number',
            'suffix' => $code,
        ]);
        CRUD::addColumn([
            'name' => 'paid',
            'label' => trans('Paid'),
            'type' => 'number',
            'suffix' => $code,
        ]);
        CRUD::addColumn([
            'name' => 'to_be_paid',
            'label' => trans('To be paid'),
            'type' => 'number',
            'suffix' => $code,
        ]);

        CRUD::filter('payment_method')
            ->label(trans('Payment method'))
            ->type('dropdown')
            ->values(OrderPaymentMethod::values());
    }
}
