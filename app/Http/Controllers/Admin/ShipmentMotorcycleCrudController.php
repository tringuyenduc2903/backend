<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EmployeePermission;
use App\Enums\OrderStatus;
use App\Http\Controllers\Admin\Operations\ProductHandoverOperation;
use App\Models\OrderMotorcycle;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class ShipmentMotorcycleCrudController
 *
 * @property-read CrudPanel $crud
 */
class ShipmentMotorcycleCrudController extends CrudController
{
    use ListOperation;
    use ProductHandoverOperation;
    use ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(OrderMotorcycle::class);
        CRUD::setRoute(route('shipment-motorcycles.index'));
        CRUD::setEntityNameStrings(trans('Shipment'), trans('Shipments'));

        CRUD::addClause('whereIn', 'status', [OrderStatus::TO_SHIP, OrderStatus::TO_RECEIVE]);

        deny_access(EmployeePermission::SHIPMENT_CRUD);
    }

    /**
     * Define what happens when the Show operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     */
    protected function setupShowOperation(): void
    {
        $this->setupListOperation();
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
            'name' => 'status',
            'label' => trans('Status'),
            'type' => 'select2_from_array',
            'options' => OrderStatus::values(),
        ]);
        CRUD::addColumn([
            'name' => 'option',
            'label' => trans('Product'),
            'attribute' => 'sku',
        ]);

        CRUD::filter('status')
            ->label(trans('Status'))
            ->type('dropdown')
            ->values([
                OrderStatus::TO_SHIP => trans('To ship'),
                OrderStatus::TO_RECEIVE => trans('To receive'),
            ]);
    }
}
