<?php

namespace App\Http\Controllers\Admin;

use Alert;
use App\Enums\EmployeePermission;
use App\Enums\OptionStatus;
use App\Enums\OrderMotorcycleLicensePlateRegistration;
use App\Enums\OrderMotorcycleRegistration;
use App\Enums\OrderPaymentMethod;
use App\Enums\OrderStatus;
use App\Enums\OrderTransactionStatus;
use App\Enums\ProductType;
use App\Events\AdminOrderMotorcycleCreatedEvent;
use App\Facades\OrderMotorcycleFee;
use App\Http\Controllers\Admin\Operations\CancelOrderOperation;
use App\Http\Controllers\Admin\Operations\FeeOperation;
use App\Http\Requests\Admin\OrderMotorcycleRequest;
use App\Models\Address;
use App\Models\Customer;
use App\Models\Identification;
use App\Models\Option;
use App\Models\OrderMotorcycle;
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
 * Class OrderMotorcycleCrudController
 *
 * @property-read CrudPanel $crud
 */
class OrderMotorcycleCrudController extends CrudController
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
        CRUD::setModel(OrderMotorcycle::class);
        CRUD::setRoute(route('order-motorcycles.index'));
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

        $fee = OrderMotorcycleFee::getFee(
            request('option'),
            request('motorcycle_registration_support'),
            request('registration_option'),
            request('license_plate_registration_option')
        );

        session(['order-motorcycle.fee' => $fee]);

        // insert item in the db
        $item = $this->crud->create($this->crud->getStrippedSaveRequest($request));
        $this->data['entry'] = $this->crud->entry = $item;

        // show a success message
        Alert::success(trans('backpack::crud.insert_success'))->flash();

        // save the redirect choice for next time
        $this->crud->setSaveAction();

        try {
            event(app(AdminOrderMotorcycleCreatedEvent::class, [
                'order' => CRUD::getCurrentEntry(),
                'employee' => backpack_user(),
            ]));
        } catch (ValidationException $exception) {
            Alert::error($exception->getMessage())->flash();
        }

        return $this->crud->performSaveAction($item->getKey());
    }

    public function fee(OrderMotorcycleRequest $request): array
    {
        return OrderMotorcycleFee::getFee(
            $request->validated('option'),
            $request->validated('motorcycle_registration_support'),
            $request->validated('registration_option'),
            $request->validated('license_plate_registration_option'),
        );
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

        // Invoice information
        CRUD::addColumn([
            'name' => 'motorcycle_registration_support',
            'label' => trans('Motorcycle registration support'),
            'type' => 'switch',
            'tab' => trans('Invoice information'),
        ]);
        CRUD::addColumn([
            'name' => 'registration_option',
            'label' => trans('Registration option'),
            'type' => 'select2_from_array',
            'options' => OrderMotorcycleRegistration::values(),
            'tab' => trans('Invoice information'),
        ]);
        CRUD::addColumn([
            'name' => 'license_plate_registration_option',
            'label' => trans('License plate registration option'),
            'type' => 'select2_from_array',
            'options' => OrderMotorcycleLicensePlateRegistration::values(),
            'tab' => trans('Invoice information'),
        ]);
        CRUD::addColumn([
            'name' => 'note',
            'label' => trans('Note'),
            'type' => 'textarea',
            'tab' => trans('Invoice information'),
        ]);
        CRUD::addColumn([
            'name' => CRUD::getModel()->getCreatedAtColumn(),
            'label' => trans('Created at'),
            'tab' => trans('Invoice information'),
        ]);
        CRUD::addColumn([
            'name' => CRUD::getModel()->getUpdatedAtColumn(),
            'label' => trans('Updated at'),
            'tab' => trans('Invoice information'),
        ]);

        // Customer information
        CRUD::addColumn([
            'name' => 'address.address_preview',
            'label' => trans('Address'),
            'type' => 'textarea',
            'tab' => trans('Customer information'),
        ]);

        // Payment information
        CRUD::addColumn([
            'name' => 'payment_checkout_url',
            'label' => trans('Checkout URL'),
            'type' => 'url',
            'tab' => trans('Payment information'),
        ]);
        CRUD::addColumn([
            'name' => 'motorcycle_registration_support_fee',
            'label' => trans('Motorcycle registration support fee'),
            'type' => 'number',
            'suffix' => $code,
            'tab' => trans('Payment information'),
        ]);
        CRUD::addColumn([
            'name' => 'registration_fee',
            'label' => trans('Registration fee'),
            'type' => 'number',
            'suffix' => $code,
            'tab' => trans('Payment information'),
        ]);
        CRUD::addColumn([
            'name' => 'motorcycle_registration_support_fee',
            'label' => trans('Motorcycle registration support fee'),
            'type' => 'number',
            'suffix' => $code,
            'tab' => trans('Payment information'),
        ]);
        CRUD::addColumn([
            'name' => 'registration_fee',
            'label' => trans('Registration fee'),
            'type' => 'number',
            'suffix' => $code,
            'tab' => trans('Payment information'),
        ]);
        CRUD::addColumn([
            'name' => 'license_plate_registration_fee',
            'label' => trans('License plate registration fee'),
            'type' => 'number',
            'suffix' => $code,
            'tab' => trans('Payment information'),
        ]);
        CRUD::addColumn([
            'name' => 'tax',
            'label' => trans('Tax'),
            'type' => 'number',
            'suffix' => $code,
            'tab' => trans('Payment information'),
        ]);
        CRUD::addColumn([
            'name' => 'handling_fee',
            'label' => trans('Handling fee'),
            'type' => 'number',
            'suffix' => $code,
            'tab' => trans('Payment information'),
        ]);
        CRUD::addColumn([
            'name' => 'total',
            'label' => trans('Total'),
            'type' => 'number',
            'suffix' => $code,
            'tab' => trans('Payment information'),
        ]);
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
            ]],
            'tab' => trans('Payment information'),
        ]);

        // Product information
        CRUD::addColumn([
            'name' => 'price',
            'label' => trans('Price'),
            'type' => 'number',
            'suffix' => $code,
            'tab' => trans('Product information'),
        ]);
        CRUD::addColumn([
            'name' => 'amount',
            'label' => trans('Amount'),
            'type' => 'number',
            'tab' => trans('Product information'),
        ]);
        CRUD::addColumn([
            'name' => 'value_added_tax',
            'label' => trans('Value added tax'),
            'type' => 'number',
            'suffix' => '%',
            'tab' => trans('Product information'),
        ]);
        CRUD::addColumn([
            'name' => 'motor_cycle',
            'label' => trans('Motor cycle'),
            'attribute' => 'chassis_number',
            'tab' => trans('Product information'),
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
        CRUD::addColumn([
            'name' => 'id',
            'label' => trans('Id'),
            'tab' => trans('Invoice information'),
        ]);
        CRUD::addColumn([
            'name' => 'address.customer_name',
            'label' => trans('Name'),
            'searchLogic' => fn (Builder $query, array $_, string $search_term): Builder => $query->orWhereHas(
                'address',
                fn (Builder $query): Builder => $query->whereLike('customer_name', "%$search_term%")
            ),
            'tab' => trans('Customer information'),
        ]);
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
            'tab' => trans('Customer information'),
        ]);
        CRUD::addColumn([
            'name' => 'payment_method',
            'label' => trans('Payment method'),
            'type' => 'select2_from_array',
            'options' => OrderPaymentMethod::values(),
            'tab' => trans('Payment information'),
        ]);
        CRUD::addColumn([
            'name' => 'status',
            'label' => trans('Status'),
            'type' => 'select2_from_array',
            'options' => OrderStatus::values(),
            'tab' => trans('Invoice information'),
        ]);
        CRUD::addColumn([
            'name' => 'option',
            'label' => trans('Product'),
            'attribute' => 'sku',
            'tab' => trans('Product information'),
        ]);

        CRUD::filter('payment_method')
            ->label(trans('Payment method'))
            ->type('dropdown')
            ->values(OrderPaymentMethod::values());
        CRUD::filter('status')
            ->label(trans('Status'))
            ->type('dropdown')
            ->values(OrderStatus::values());
        CRUD::addFilter(
            [
                'name' => 'option_id',
                'label' => trans('Product'),
                'type' => 'select2_ajax',
                'minimum_input_length' => 0,
                'method' => 'POST',
                'select_attribute' => 'sku',
            ],
            route('order-motorcycles.fetchOptions')
        );
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
        CRUD::setValidation(OrderMotorcycleRequest::class);

        Widget::add([
            'type' => 'script',
            'content' => resource_path('assets/js/admin/forms/order.js'),
        ]);

        Widget::add([
            'type' => 'script',
            'content' => resource_path('assets/js/admin/forms/fee/order-motorcycle.js'),
        ]);

        $code = current_currency();

        CRUD::addField([
            'name' => 'option',
            'label' => trans('Product'),
            'minimum_input_length' => 0,
            'data_source' => route('order-motorcycles.fetchOptions'),
            'attribute' => 'sku',
            'tab' => trans('Price quote'),
        ]);
        CRUD::field('motorcycle_registration_support')
            ->label(trans('Motorcycle registration support'))
            ->type('switch')
            ->tab(trans('Price quote'));
        CRUD::addField([
            'name' => 'registration_option',
            'label' => trans('Registration option'),
            'type' => 'select2_from_array',
            'options' => OrderMotorcycleRegistration::values(),
            'tab' => trans('Price quote'),
        ]);
        CRUD::addField([
            'name' => 'license_plate_registration_option',
            'label' => trans('License plate registration option'),
            'type' => 'select2_from_array',
            'options' => OrderMotorcycleLicensePlateRegistration::values(),
            'tab' => trans('Price quote'),
        ]);
        CRUD::addField([
            'name' => 'payment_method',
            'label' => trans('Payment method'),
            'type' => 'select2_from_array',
            'options' => OrderPaymentMethod::values(),
            'tab' => trans('Price quote'),
        ]);
        CRUD::addField([
            'name' => 'fee',
            'label' => trans('View order quote'),
            'type' => 'view',
            'view' => 'crud.buttons.order.fee',
            'route' => route('order-motorcycles.fee'),
            'notification' => [
                'successfully' => [
                    'title' => trans('Price quote'),
                    'description' => trans('Retrieve information successfully.'),
                ],
            ],
            'tab' => trans('Price quote'),
        ]);
        CRUD::field('price')
            ->label(trans('Price'))
            ->hint(1)
            ->attributes([
                'disabled' => true,
            ])
            ->prefix($code)
            ->tab(trans('Price quote'));
        CRUD::field('value_added_tax')
            ->label(trans('Value added tax'))
            ->type('number')
            ->hint(2)
            ->attributes([
                'disabled' => true,
            ])
            ->prefix('%')
            ->tab(trans('Price quote'));
        CRUD::field('motorcycle_registration_support_fee')
            ->label(trans('Motorcycle registration support fee'))
            ->attributes([
                'readonly' => true,
            ])
            ->prefix($code)
            ->hint(3)
            ->tab(trans('Price quote'));
        CRUD::field('registration_fee')
            ->label(trans('Registration fee'))
            ->attributes([
                'readonly' => true,
            ])
            ->prefix($code)
            ->hint(4)
            ->tab(trans('Price quote'));
        CRUD::field('license_plate_registration_fee')
            ->label(trans('License plate registration fee'))
            ->attributes([
                'readonly' => true,
            ])
            ->prefix($code)
            ->hint(5)
            ->tab(trans('Price quote'));
        CRUD::field('tax')
            ->label(trans('Tax'))
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
            ->hint('8 = 1 + 3 + 4 + 5 + 7')
            ->tab(trans('Price quote'));
        CRUD::addField([
            'name' => 'customer',
            'label' => trans('Customer'),
            'inline_create' => [
                'create_route' => route('customers-inline-create-save'),
                'modal_route' => route('customers-inline-create'),
            ],
            'data_source' => route('order-motorcycles.fetchCustomers'),
            'minimum_input_length' => 0,
            'attribute' => 'phone_number',
            'tab' => trans('Customer'),
        ]);
        CRUD::addField([
            'name' => 'address',
            'label' => trans('Address'),
            'data_source' => route('order-motorcycles.fetchAddresses'),
            'minimum_input_length' => 0,
            'dependencies' => 'customer',
            'attribute' => 'address_detail',
            'tab' => trans('Customer'),
        ]);
        CRUD::addField([
            'name' => 'identification',
            'label' => trans('Identification'),
            'data_source' => route('order-motorcycles.fetchIdentifications'),
            'minimum_input_length' => 0,
            'dependencies' => 'customer',
            'attribute' => 'number',
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
                            ->whereType(ProductType::MOTOR_CYCLE);
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

    protected function fetchIdentifications()
    {
        return $this->fetch([
            'model' => Identification::class,
            'query' => function (Identification $identification): Builder|Identification {
                $form = collect(request('form'))
                    ->pluck('value', 'name');

                /** @var Identification $identification */
                return isset($form['customer'])
                    ? $identification->whereCustomerId($form['customer'])
                    : $identification;
            },
            'searchable_attributes' => ['number'],
        ]);
    }
}
