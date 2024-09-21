<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EmployeePermissionEnum;
use App\Enums\OptionStatus;
use App\Enums\OrderShippingType;
use App\Enums\OrderTransactionType;
use App\Enums\ProductType;
use App\Http\Requests\Admin\OrderRequest;
use App\Models\Address;
use App\Models\Customer;
use App\Models\Option;
use App\Models\Order;
use App\Models\Product;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\Pro\Http\Controllers\Operations\FetchOperation;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class OrderCrudController
 *
 * @property-read CrudPanel $crud
 */
class OrderCrudController extends CrudController
{
    use CreateOperation;
    use FetchOperation;
    use ListOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(Order::class);
        CRUD::setRoute(route('orders.index'));
        CRUD::setEntityNameStrings(trans('Order'), trans('Orders'));

        deny_access(EmployeePermissionEnum::ORDER_CRUD);
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
    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     *
     * @return void
     */
    protected function setupUpdateOperation()
    {
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     *
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(OrderRequest::class);

        $code = current_currency();

        CRUD::addField([
            'name' => 'options',
            'label' => trans('Products'),
            'type' => 'repeatable',
            'subfields' => [[
                'name' => 'option',
                'label' => trans('Product'),
                'minimum_input_length' => 0,
                'data_source' => route('orders.fetchOptions'),
                'attribute' => 'sku',
            ], [
                'name' => 'price',
                'label' => trans('Price'),
                'hint' => 1,
                'attributes' => [
                    'disabled' => true,
                ],
                'prefix' => $code . ' ',
                'wrapper' => [
                    'class' => 'form-group col-sm-12 col-md-4 col-xl-2',
                ],
            ], [
                'name' => 'amount',
                'label' => trans('Amount'),
                'type' => 'number',
                'default' => 1,
                'hint' => 2,
                'wrapper' => [
                    'class' => 'form-group col-sm-12 col-md-4 col-xl-2',
                ],
            ], [
                'name' => 'pretax_price',
                'label' => trans('Pretax price'),
                'hint' => '3 = 1 x 2',
                'attributes' => [
                    'disabled' => true,
                ],
                'prefix' => $code . ' ',
                'wrapper' => [
                    'class' => 'form-group col-sm-12 col-md-4 col-xl-2',
                ],
            ], [
                'name' => 'value_added_tax',
                'label' => trans('Value added tax'),
                'type' => 'number',
                'hint' => '4',
                'attributes' => [
                    'disabled' => true,
                ],
                'prefix' => '%',
                'wrapper' => [
                    'class' => 'form-group col-sm-12 col-md-4 col-xl-2',
                ],
            ], [
                'name' => 'value_added_tax_preview',
                'label' => trans('Value added tax preview'),
                'hint' => '5 = 3 x 4',
                'attributes' => [
                    'disabled' => true,
                ],
                'prefix' => $code . ' ',
                'wrapper' => [
                    'class' => 'form-group col-sm-12 col-md-4 col-xl-2',
                ],
            ], [
                'name' => 'price_after_tax',
                'label' => trans('Price after tax'),
                'type' => 'text',
                'hint' => '6 = 3 + 5',
                'attributes' => [
                    'disabled' => true,
                ],
                'prefix' => $code . ' ',
                'wrapper' => [
                    'class' => 'form-group col-sm-12 col-md-4 col-xl-2',
                ],
            ]],
            'min_rows' => 1,
            'max_rows' => 20,
            'reorder' => false,
            'tab' => trans('Price quote'),
        ]);
        CRUD::addField([
            'name' => 'shipping_type',
            'label' => trans('Shipping type'),
            'type' => 'select2_from_array',
            'options' => OrderShippingType::values(),
            'tab' => trans('Price quote'),
        ]);
        CRUD::addField([
            'name' => 'transaction_type',
            'label' => trans('Transaction type'),
            'type' => 'select2_from_array',
            'options' => OrderTransactionType::values(),
            'tab' => trans('Price quote'),
        ]);
        CRUD::field('tax')
            ->label(trans('Tax'))
            ->attributes([
                'readonly' => true,
            ])
            ->prefix($code . ' ')
            ->hint(10)
            ->tab(trans('Price quote'));
        CRUD::field('shipping_fee')
            ->label(trans('Shipping fee'))
            ->attributes([
                'readonly' => true,
            ])
            ->prefix($code . ' ')
            ->hint(11)
            ->tab(trans('Price quote'));
        CRUD::field('handling_fee')
            ->label(trans('Handling fee'))
            ->attributes([
                'readonly' => true,
            ])
            ->prefix($code . ' ')
            ->hint('12 = 7 + 8 + 9')
            ->tab(trans('Price quote'));
        CRUD::field('total')
            ->label(trans('Total'))
            ->attributes([
                'readonly' => true,
            ])
            ->prefix($code . ' ')
            ->hint('13 = 6 + 11 + 12 + 13')
            ->tab(trans('Price quote'));
        CRUD::addField([
            'name' => 'customer',
            'label' => trans('Customer'),
            'inline_create' => [
                'create_route' => route('customers-inline-create-save'),
                'modal_route' => route('customers-inline-create'),
            ],
            'data_source' => route('orders.fetchCustomers'),
            'minimum_input_length' => 0,
            'attribute' => 'phone_number',
            'tab' => trans('Customer'),
        ]);
        CRUD::addField([
            'name' => 'address',
            'label' => trans('Address'),
            'data_source' => route('orders.fetchAddresses'),
            'minimum_input_length' => 0,
            'dependencies' => 'customer',
            'attribute' => 'address_detail',
            'tab' => trans('Customer'),
        ]);

        CRUD::field('note')
            ->label(trans('Note'))
            ->type('textarea')
            ->tab(trans('Customer'));
    }

    protected function fetchOptions()
    {
        return $this->fetch([
            'model' => Option::class,
            'query' => fn(Option $option): Builder|Option => $option
                ->whereStatus(OptionStatus::IN_STOCK)
                ->whereHas(
                    'product',
                    function (Builder $query) {
                        /** @var Product $query */
                        $query
                            ->wherePublished(true)
                            ->whereNot('type', ProductType::MOTOR_CYCLE);
                    }
                ),
            'searchable_attributes' => ['sku'],
        ]);
    }

    protected function fetchCustomers()
    {
        return $this->fetch([
            'model' => Customer::class,
            'query' => function (Customer $customer): Builder|Customer {
                return $customer->withTrashed();
            },
            'searchable_attributes' => ['phone_number'],
        ]);
    }

    protected function fetchAddresses()
    {
        return $this->fetch([
            'model' => Address::class,
            'query' => function (Address $address): Builder|Address {
                $form = collect(request('form'))
                    ->pluck('value', 'name');

                /** @var Address $address */
                return isset($form['customer'])
                    ? $address->whereCustomerId($form['customer'])
                    : $address;
            },
            'searchable_attributes' => ['address_detail'],
        ]);
    }
}
