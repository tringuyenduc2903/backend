<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CustomerGender;
use App\Enums\EmployeePermissionEnum;
use App\Http\Requests\Admin\CustomerRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\Pro\Http\Controllers\Operations\BulkTrashOperation;
use Backpack\Pro\Http\Controllers\Operations\TrashOperation;

/**
 * Class CustomerCrudController
 *
 * @property-read CrudPanel $crud
 */
class CustomerCrudController extends CrudController
{
    use BulkTrashOperation;
    use CreateOperation;
    use ListOperation;
    use TrashOperation;
    use UpdateOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(config('auth.providers.users.model'));
        CRUD::setRoute(backpack_url('customers'));
        CRUD::setEntityNameStrings(trans('Customer'), trans('Customers'));

        deny_access(EmployeePermissionEnum::CUSTOMER_CRUD);
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
     *
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
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
    }
}
