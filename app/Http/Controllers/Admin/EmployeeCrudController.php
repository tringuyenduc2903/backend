<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EmployeePermissionEnum;
use App\Http\Requests\Admin\EmployeeStoreCrudRequest;
use App\Http\Requests\Admin\EmployeeUpdateCrudRequest;
use App\Models\Branch;
use App\Models\Role;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\Pro\Http\Controllers\Operations\BulkTrashOperation;
use Backpack\Pro\Http\Controllers\Operations\FetchOperation;
use Backpack\Pro\Http\Controllers\Operations\TrashOperation;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class EmployeeCrudController
 *
 * @property-read CrudPanel $crud
 */
class EmployeeCrudController extends CrudController
{
    use BulkTrashOperation;
    use CreateOperation;
    use FetchOperation;
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
        CRUD::setModel(config('auth.providers.employees.model'));
        CRUD::setRoute(route('employees.index'));
        CRUD::setEntityNameStrings(trans('Employee'), trans('Employees'));

        deny_access(EmployeePermissionEnum::EMPLOYEE_CRUD);
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
            ->label(trans('Email'));
        CRUD::column('branch')
            ->label(trans('Branch'));
        CRUD::column('roles')
            ->label(trans('Roles'));

        CRUD::filter('name')
            ->label(trans('Name'))
            ->type('text');
        CRUD::filter('email')
            ->label(trans('Email'))
            ->type('text');
        CRUD::addFilter(
            [
                'name' => 'role_id',
                'label' => trans('Role'),
                'type' => 'select2_ajax',
                'minimum_input_length' => 0,
                'method' => 'POST',
            ],
            route('employees.fetchRoles'),
            fn (string $values) => CRUD::addClause(
                'whereHas',
                'roles',
                fn (Builder $query): Builder => $query->where('role_id', json_decode($values))
            )
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
        CRUD::setValidation(EmployeeStoreCrudRequest::class);

        $this->employeeFields();
    }

    /**
     * @return void
     */
    protected function employeeFields()
    {
        CRUD::field('name')
            ->label(trans('Name'))
            ->tab(trans('Employee info'));
        CRUD::field('email')
            ->label(trans('Email'))
            ->type('email')
            ->tab(trans('Employee info'));
        CRUD::field('password')
            ->label(trans('Password'))
            ->type('password')
            ->tab(trans('Employee info'));
        CRUD::field('password_confirmation')
            ->label(trans('Password confirmation'))
            ->type('password')
            ->tab(trans('Employee info'));
        CRUD::addField([
            'name' => 'branch',
            'label' => trans('Branch'),
            'inline_create' => [
                'create_route' => route('branches-inline-create-save'),
                'modal_route' => route('branches-inline-create'),
            ],
            'data_source' => route('employees.fetchBranches'),
            'minimum_input_length' => 0,
            'tab' => trans('Employee role'),
        ]);
        CRUD::addField([
            'name' => 'roles',
            'label' => trans('Roles'),
            'data_source' => route('employees.fetchRoles'),
            'minimum_input_length' => 0,
            'inline_create' => [
                'create_route' => route('roles-inline-create-save'),
                'modal_route' => route('roles-inline-create'),
            ],
            'tab' => trans('Employee role'),
        ]);
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
        CRUD::setValidation(EmployeeUpdateCrudRequest::class);

        $this->employeeFields();
    }

    protected function fetchBranches()
    {
        return $this->fetch(Branch::class);
    }

    protected function fetchRoles()
    {
        return $this->fetch([
            'model' => Role::class,
            'query' => fn (Role $role): Role|Builder => $role
                ->whereGuardName(config('backpack.base.guard')),
        ]);
    }
}
