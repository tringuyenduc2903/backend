<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CustomerAddress;
use App\Enums\CustomerGender;
use App\Enums\CustomerIdentification;
use App\Enums\EmployeePermissionEnum;
use App\Http\Requests\Admin\CustomerRequest;
use App\Models\District;
use App\Models\Province;
use App\Models\Ward;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\Pro\Http\Controllers\Operations\BulkTrashOperation as V2;
use Backpack\Pro\Http\Controllers\Operations\FetchOperation;
use Backpack\Pro\Http\Controllers\Operations\TrashOperation as V1;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class CustomerCrudController
 *
 * @property-read CrudPanel $crud
 */
class CustomerCrudController extends CrudController
{
    use CreateOperation;
    use FetchOperation;
    use ListOperation;
    use UpdateOperation;
    use V1;
    use V2;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     */
    public function setup(): void
    {
        CRUD::setModel(config('auth.providers.users.model'));
        CRUD::setRoute(route('customers.index'));
        CRUD::setEntityNameStrings(trans('Customer'), trans('Customers'));

        deny_access(EmployeePermissionEnum::CUSTOMER_CRUD);
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     */
    protected function setupListOperation(): void
    {
        CRUD::column('name')
            ->label(trans('Name'));
        CRUD::column('email')
            ->label(trans('Email'))
            ->type('email');
        CRUD::column('phone_number')
            ->label(trans('Phone number'))
            ->type('phone');
        CRUD::column('birthday')
            ->label(trans('Birthday'));
        CRUD::addColumn([
            'name' => 'gender',
            'label' => trans('Gender'),
            'type' => 'select2_from_array',
            'options' => CustomerGender::values(),
        ]);

        CRUD::filter('name')
            ->label(trans('Name'))
            ->type('text');
        CRUD::filter('email')
            ->label(trans('Email'))
            ->type('text');
        CRUD::filter('phone_number')
            ->label(trans('Phone number'))
            ->type('text');
        CRUD::filter('birthday')
            ->label(trans('Birthday'))
            ->type('date');
        CRUD::filter('gender')
            ->label(trans('Gender'))
            ->type('dropdown')
            ->values(CustomerGender::values());
    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     */
    protected function setupUpdateOperation(): void
    {
        $this->setupCreateOperation();
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     */
    protected function setupCreateOperation(): void
    {
        CRUD::setValidation(CustomerRequest::class);

        CRUD::field('name')
            ->label(trans('Name'));
        CRUD::field('email')
            ->label(trans('Email'))
            ->type('email');
        CRUD::field('phone_number')
            ->label(trans('Phone number'))
            ->type('phone');
        CRUD::field('birthday')
            ->label(trans('Birthday'));
        CRUD::addField([
            'name' => 'gender',
            'label' => trans('Gender'),
            'type' => 'select2_from_array',
            'options' => CustomerGender::values(),
        ]);
        CRUD::addField([
            'name' => 'timezone',
            'label' => trans('Timezone'),
            'type' => 'select2_from_array',
            'options' => timezone_identifiers_list(),
            'default' => array_search(
                config('app.timezone'),
                timezone_identifiers_list(),
            ),
        ]);
        CRUD::addField([
            'name' => 'addresses',
            'label' => trans('Addresses'),
            'type' => 'repeatable',
            'subfields' => [[
                'name' => 'default',
                'label' => trans('Set as default'),
                'type' => 'switch',
                'wrapper' => [
                    'class' => 'form-group col-sm-12 d-flex justify-content-end',
                ],
            ], [
                'name' => 'type',
                'label' => trans('Type'),
                'type' => 'select2_from_array',
                'options' => CustomerAddress::values(),
                'allows_null' => false,
            ], [
                'name' => 'customer_name',
                'label' => trans('Name'),
            ], [
                'name' => 'customer_phone_number',
                'label' => trans('Phone number'),
                'type' => 'phone',
            ], [
                'name' => 'country',
                'label' => trans('Country'),
                'default' => 'Việt Nam',
                'wrapper' => [
                    'class' => 'form-group col-sm-12 col-md-6 mb-3',
                ],
            ], [
                'name' => 'province',
                'label' => trans('Province'),
                'model' => Province::class,
                'entity' => 'province',
                'data_source' => route('customers.fetchProvinces'),
                'minimum_input_length' => 0,
                'method' => 'POST',
                'wrapper' => [
                    'class' => 'form-group col-sm-12 col-md-6',
                ],
            ], [
                'name' => 'district',
                'label' => trans('District'),
                'model' => District::class,
                'entity' => 'district',
                'data_source' => route('customers.fetchDistricts'),
                'minimum_input_length' => 0,
                'method' => 'POST',
                'dependencies' => 'province',
                'include_all_form_fields' => true,
                'wrapper' => [
                    'class' => 'form-group col-sm-12 col-md-6',
                ],
            ], [
                'name' => 'ward',
                'label' => trans('Ward'),
                'model' => Ward::class,
                'entity' => 'ward',
                'data_source' => route('customers.fetchWards'),
                'minimum_input_length' => 0,
                'method' => 'POST',
                'dependencies' => ['province', 'district'],
                'include_all_form_fields' => true,
                'wrapper' => [
                    'class' => 'form-group col-sm-12 col-md-6',
                ],
            ], [
                'name' => 'address_detail',
                'label' => trans('Address detail'),
                'type' => 'textarea',
            ]],
            'max_rows' => 5,
            'reorder' => false,
        ]);
        CRUD::addField([
            'name' => 'identifications',
            'label' => trans('Identifications'),
            'type' => 'repeatable',
            'subfields' => [[
                'name' => 'default',
                'label' => trans('Set as default'),
                'type' => 'switch',
                'wrapper' => [
                    'class' => 'form-group col-sm-12 d-flex justify-content-end',
                ],
            ], [
                'name' => 'type',
                'label' => trans('Type'),
                'type' => 'select2_from_array',
                'options' => CustomerIdentification::values(),
                'allows_null' => false,
            ], [
                'name' => 'number',
                'label' => trans('Number'),
            ], [
                'name' => 'issued_name',
                'label' => trans('Issued name'),
            ], [
                'name' => 'issuance_date',
                'label' => trans('Issued date'),
                'type' => 'date',
                'wrapper' => [
                    'class' => 'form-group col-sm-12 col-md-6 mb-3',
                ],
            ], [
                'name' => 'expiry_date',
                'label' => trans('Expiry date'),
                'type' => 'date',
                'wrapper' => [
                    'class' => 'form-group col-sm-12 col-md-6 mb-3',
                ],
            ]],
            'max_rows' => 5,
            'reorder' => false,
        ]);
    }

    protected function fetchProvinces()
    {
        return $this->fetch(Province::class);
    }

    protected function fetchDistricts()
    {
        return $this->fetch([
            'model' => District::class,
            'query' => function (District $district): District|Builder {
                $form = collect(request('form'))->pluck('value', 'name');

                return isset($form['addresses[0][province]'])
                    ? $district->whereProvinceId($form['addresses[0][province]'])
                    : $district;
            },
        ]);
    }

    protected function fetchWards()
    {
        return $this->fetch([
            'model' => Ward::class,
            'query' => function (Ward $ward): Ward|Builder {
                $form = collect(request('form'))->pluck('value', 'name');

                return isset($form['addresses[0][district]'])
                    ? $ward->whereDistrictId($form['addresses[0][district]'])
                    : $ward;
            },
        ]);
    }
}
