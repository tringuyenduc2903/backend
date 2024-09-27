<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EmployeePermissionEnum;
use App\Enums\OptionStatus;
use App\Enums\OrderPaymentMethod;
use App\Enums\OrderShippingMethod;
use App\Enums\OrderStatus;
use App\Enums\ProductType;
use App\Events\OrderCreatedEvent;
use App\Http\Controllers\Admin\Operations\CancelOrderOperation;
use App\Http\Requests\Admin\OrderRequest;
use App\Models\Address;
use App\Models\Customer;
use App\Models\Option;
use App\Models\Order;
use App\Models\Product;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\Pro\Http\Controllers\Operations\FetchOperation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;

/**
 * Class OrderCrudController
 *
 * @property-read CrudPanel $crud
 */
class OrderCrudController extends CrudController
{
    use CancelOrderOperation;
    use CreateOperation {
        store as traitStore;
    }
    use FetchOperation;
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
        CRUD::setRoute(route('orders.index'));
        CRUD::setEntityNameStrings(trans('Order'), trans('Orders'));

        CRUD::operation(
            ['list', 'show'],
            fn () => CRUD::addButton('line', 'cancel_order', 'view', 'crud.buttons.order.cancel_order', 'end'));
        CRUD::setAccessCondition(
            'cancel_order',
            fn (Order $entry) => $entry->status == OrderStatus::TO_PAY
        );

        deny_access(EmployeePermissionEnum::ORDER_CRUD);
    }

    /**
     * Store a newly created resource in the database.
     */
    public function store(): RedirectResponse
    {
        $response = $this->traitStore();

        event(app(OrderCreatedEvent::class, [
            'order' => CRUD::getCurrentEntry(),
            'employee' => backpack_user(),
        ]));

        return $response;
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
                    fn (Builder $query): Builder => $query->whereLike(
                        'customer_name',
                        "%$search_term%"
                    )
                )
            );
        CRUD::column('address.customer_phone_number')
            ->label(trans('Phone number'))
            ->type('phone')
            ->searchLogic(
                fn (Builder $query, array $_, string $search_term): Builder => $query->orWhereHas(
                    'address',
                    fn (Builder $query): Builder => $query->whereLike(
                        'customer_phone_number',
                        "%$search_term%"
                    )
                )
            );
        CRUD::addColumn([
            'name' => 'shipping_method',
            'label' => trans('Shipping method'),
            'type' => 'select2_from_array',
            'options' => OrderShippingMethod::values(),
        ]);
        CRUD::addColumn([
            'name' => 'payment_method',
            'label' => trans('Payment method'),
            'type' => 'select2_from_array',
            'options' => OrderPaymentMethod::values(),
        ]);
        CRUD::addColumn([
            'name' => 'status',
            'label' => trans('Status'),
            'type' => 'select2_from_array',
            'options' => OrderStatus::values(),
        ]);
        CRUD::addColumn([
            'name' => 'shipping_code',
            'label' => trans('Shipping code'),
            'wrapper' => [
                'href' => fn ($_, $__, $entry): string => sprintf(
                    'https://donhang.ghn.vn/?order_code=%s',
                    $entry->shipping_code
                ),
            ],
        ]);

        CRUD::filter('shipping_method')
            ->label(trans('Shipping method'))
            ->type('dropdown')
            ->values(OrderShippingMethod::values());
        CRUD::filter('payment_method')
            ->label(trans('Payment method'))
            ->type('dropdown')
            ->values(OrderPaymentMethod::values());
        CRUD::filter('status')
            ->label(trans('Status'))
            ->type('dropdown')
            ->values(OrderStatus::values());
    }

    /**
     * Define what happens when the Show operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     */
    protected function setupShowOperation(): void
    {
        $code = current_currency();

        CRUD::column('id')
            ->label(trans('Id'));
        CRUD::column('address.customer_name')
            ->label(trans('Name'));
        CRUD::column('address.customer_phone_number')
            ->label(trans('Phone number'))
            ->type('phone');
        CRUD::column('address.address_preview')
            ->label(trans('Address'));
        CRUD::addColumn([
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
                'type' => 'number',
                'suffix' => ' '.$code,
            ], [
                'name' => 'amount',
                'label' => trans('Amount'),
                'type' => 'number',
            ], [
                'name' => 'value_added_tax',
                'label' => trans('Value added tax'),
                'type' => 'number',
                'suffix' => '%',
            ]],
            'reorder' => false,
        ]);
        CRUD::addColumn([
            'name' => 'tax',
            'label' => trans('Tax'),
            'type' => 'number',
            'suffix' => ' '.$code,
        ]);
        CRUD::addColumn([
            'name' => 'shipping_fee',
            'label' => trans('Shipping fee'),
            'type' => 'number',
            'suffix' => ' '.$code,
        ]);
        CRUD::addColumn([
            'name' => 'handling_fee',
            'label' => trans('Handling fee'),
            'type' => 'number',
            'suffix' => ' '.$code,
        ]);
        CRUD::addColumn([
            'name' => 'total',
            'label' => trans('Total'),
            'type' => 'number',
            'suffix' => ' '.$code,
        ]);
        CRUD::column('note')
            ->label(trans('Note'))
            ->type('textarea');
        CRUD::addColumn([
            'name' => 'shipping_method',
            'label' => trans('Shipping method'),
            'type' => 'select2_from_array',
            'options' => OrderShippingMethod::values(),
        ]);
        CRUD::addColumn([
            'name' => 'payment_method',
            'label' => trans('Payment method'),
            'type' => 'select2_from_array',
            'options' => OrderPaymentMethod::values(),
        ]);
        CRUD::addColumn([
            'name' => 'status',
            'label' => trans('Status'),
            'type' => 'select2_from_array',
            'options' => OrderStatus::values(),
        ]);

        // if the model has timestamps, add columns for created_at and updated_at
        if (CRUD::get('show.timestamps') && CRUD::getModel()->usesTimestamps()) {
            CRUD::column(CRUD::getModel()->getCreatedAtColumn())
                ->label(trans('Created at'));
            CRUD::column(CRUD::getModel()->getUpdatedAtColumn())
                ->label(trans('Updated at'));
        }
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
                'prefix' => $code.' ',
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
                'prefix' => $code.' ',
                'wrapper' => [
                    'class' => 'form-group col-sm-12 col-md-4 col-xl-2',
                ],
            ], [
                'name' => 'value_added_tax',
                'label' => trans('Value added tax'),
                'type' => 'number',
                'hint' => 4,
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
                'prefix' => $code.' ',
                'wrapper' => [
                    'class' => 'form-group col-sm-12 col-md-4 col-xl-2',
                ],
            ], [
                'name' => 'price_after_tax',
                'label' => trans('Price after tax'),
                'hint' => '6 = 3 + 5',
                'attributes' => [
                    'disabled' => true,
                ],
                'prefix' => $code.' ',
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
            'name' => 'shipping_method',
            'label' => trans('Shipping method'),
            'type' => 'select2_from_array',
            'options' => OrderShippingMethod::values(),
            'tab' => trans('Price quote'),
        ]);
        CRUD::addField([
            'name' => 'payment_method',
            'label' => trans('Payment method'),
            'type' => 'select2_from_array',
            'options' => OrderPaymentMethod::values(),
            'tab' => trans('Price quote'),
        ]);
        CRUD::field('tax')
            ->label(trans('Tax'))
            ->attributes([
                'readonly' => true,
            ])
            ->prefix($code.' ')
            ->hint(10)
            ->tab(trans('Price quote'));
        CRUD::field('shipping_fee')
            ->label(trans('Shipping fee'))
            ->attributes([
                'readonly' => true,
            ])
            ->prefix($code.' ')
            ->hint(11)
            ->tab(trans('Price quote'));
        CRUD::field('handling_fee')
            ->label(trans('Handling fee'))
            ->attributes([
                'readonly' => true,
            ])
            ->prefix($code.' ')
            ->hint('12 = 7 + 8 + 9')
            ->tab(trans('Price quote'));
        CRUD::field('total')
            ->label(trans('Total'))
            ->attributes([
                'readonly' => true,
            ])
            ->prefix($code.' ')
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
            'query' => fn (Option $option): Builder|Option => $option
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
