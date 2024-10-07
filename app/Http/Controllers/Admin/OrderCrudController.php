<?php

namespace App\Http\Controllers\Admin;

use Alert;
use App\Enums\EmployeePermission;
use App\Enums\OptionStatus;
use App\Enums\OrderPaymentMethod;
use App\Enums\OrderShippingMethod;
use App\Enums\OrderStatus;
use App\Enums\OrderTransactionStatus;
use App\Enums\ProductType;
use App\Events\AdminOrderCreatedEvent;
use App\Facades\OrderFee;
use App\Http\Controllers\Admin\Operations\CancelOrderOperation;
use App\Http\Controllers\Admin\Operations\FeeOperation;
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
use Backpack\CRUD\app\Library\Widget;
use Backpack\Pro\Http\Controllers\Operations\FetchOperation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

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
    use FeeOperation;
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

        deny_access(EmployeePermission::ORDER_CRUD);
    }

    /**
     * Store a newly created resource in the database.
     */
    public function store(): RedirectResponse
    {
        $this->crud->hasAccessOrFail('create');

        // execute the FormRequest authorization and validation, if one is required
        $request = $this->crud->validateRequest();

        // register any Model Events defined on fields
        $this->crud->registerFieldEvents();

        $fee = OrderFee::getFee(
            request('options'),
            request('shipping_method'),
            request('address')
        );

        session(['order.fee' => $fee]);

        // insert item in the db
        $item = $this->crud->create($this->crud->getStrippedSaveRequest($request));
        $this->data['entry'] = $this->crud->entry = $item;

        // show a success message
        Alert::success(trans('backpack::crud.insert_success'))->flash();

        // save the redirect choice for next time
        $this->crud->setSaveAction();

        try {
            event(app(AdminOrderCreatedEvent::class, [
                'order' => CRUD::getCurrentEntry(),
                'employee' => backpack_user(),
            ]));
        } catch (ValidationException $exception) {
            Alert::error($exception->getMessage())->flash();
        }

        return $this->crud->performSaveAction($item->getKey());
    }

    public function fee(OrderRequest $request): array
    {
        return OrderFee::getFee(
            $request->validated('options'),
            $request->validated('shipping_method'),
            $request->validated('address')
        );
    }

    /**
     * Define what happens when the Show operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     */
    public function setupShowOperation(): void
    {
        $this->setupListOperation();

        $code = ' '.current_currency();

        CRUD::column('address.address_preview')
            ->label(trans('Address'))
            ->type('textarea')
            ->after('address');
        CRUD::addColumn([
            'name' => 'shipments',
            'label' => trans('Shipments'),
            'subfields' => [[
                'name' => 'name_preview',
                'label' => trans('Name'),
            ], [
                'name' => 'description',
                'label' => trans('Description'),
            ], [
                'name' => 'reason_preview',
                'label' => trans('Reason'),
                'type' => 'textarea',
            ]],
        ])->afterColumn('shipping_method');
        CRUD::addColumn([
            'name' => 'shipping_code',
            'label' => trans('Shipping code'),
            'wrapper' => [
                'href' => fn ($_, $__, $entry): string => sprintf(
                    'https://donhang.ghn.vn/?order_code=%s',
                    $entry->shipping_code
                ),
            ],
        ])->afterColumn('shipping_method');
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
        ])->afterColumn('payment_method');
        CRUD::column('payment_checkout_url')
            ->label(trans('Checkout URL'))
            ->type('url')
            ->after('payment_method');
        CRUD::column('note')
            ->label(trans('Note'))
            ->type('textarea');
        CRUD::addColumn([
            'name' => 'options',
            'label' => trans('Products'),
            'subfields' => [[
                'name' => 'option',
                'label' => trans('Product'),
                'attribute' => 'sku',
            ], [
                'name' => 'price',
                'label' => trans('Price'),
                'type' => 'number',
                'suffix' => $code,
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
        ]);
        CRUD::addColumn([
            'name' => 'tax',
            'label' => trans('Tax'),
            'type' => 'number',
            'suffix' => $code,
        ]);
        CRUD::addColumn([
            'name' => 'shipping_fee',
            'label' => trans('Shipping fee'),
            'type' => 'number',
            'suffix' => $code,
        ]);
        CRUD::addColumn([
            'name' => 'handling_fee',
            'label' => trans('Handling fee'),
            'type' => 'number',
            'suffix' => $code,
        ]);
        CRUD::addColumn([
            'name' => 'total',
            'label' => trans('Total'),
            'type' => 'number',
            'suffix' => $code,
        ]);
        CRUD::column(CRUD::getModel()->getUpdatedAtColumn())
            ->label(trans('Updated at'))
            ->after(CRUD::getModel()->getCreatedAtColumn());
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     *
     * @return void
     */
    public function setupListOperation()
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
        CRUD::column(CRUD::getModel()->getCreatedAtColumn())
            ->label(trans('Created at'));

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
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     *
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(OrderRequest::class);

        Widget::add([
            'type' => 'script',
            'content' => resource_path('assets/js/admin/forms/fee/order.js'),
        ]);

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
                'prefix' => $code,
                'wrapper' => [
                    'class' => 'form-group col-sm-12 col-md-3',
                ],
            ], [
                'name' => 'amount',
                'label' => trans('Amount'),
                'type' => 'number',
                'default' => 1,
                'hint' => 2,
                'wrapper' => [
                    'class' => 'form-group col-sm-12 col-md-3',
                ],
            ], [
                'name' => 'value_added_tax',
                'label' => trans('Value added tax'),
                'type' => 'number',
                'hint' => 3,
                'attributes' => [
                    'disabled' => true,
                ],
                'prefix' => '%',
                'wrapper' => [
                    'class' => 'form-group col-sm-12 col-md-3',
                ],
            ], [
                'name' => 'make_money',
                'label' => trans('Make money'),
                'hint' => '4 = 1 * 2',
                'attributes' => [
                    'disabled' => true,
                ],
                'prefix' => $code,
                'wrapper' => [
                    'class' => 'form-group col-sm-12 col-md-3',
                ],
            ]],
            'min_rows' => 1,
            'max_rows' => 20,
            'reorder' => false,
            'tab' => trans('Price quote'),
        ]);
        CRUD::addField([
            'name' => 'fee',
            'label' => trans('View order quote'),
            'type' => 'view',
            'view' => 'crud.buttons.order.fee',
            'route' => route('orders.fee'),
            'notification' => [
                'successfully' => [
                    'title' => trans('Price quote'),
                    'description' => trans('Retrieve information successfully.'),
                ],
            ],
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
            ->prefix($code)
            ->hint(5)
            ->tab(trans('Price quote'));
        CRUD::field('shipping_fee')
            ->label(trans('Shipping fee'))
            ->attributes([
                'readonly' => true,
            ])
            ->prefix($code)
            ->hint(6)
            ->tab(trans('Price quote'));
        CRUD::field('handling_fee')
            ->label(trans('Handling fee'))
            ->attributes([
                'readonly' => true,
            ])
            ->prefix($code)
            ->hint(7)
            ->tab(trans('Price quote'));
        CRUD::field('total')
            ->label(trans('Total'))
            ->attributes([
                'readonly' => true,
            ])
            ->prefix($code)
            ->hint('8 = 4 + 6 + 7')
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
