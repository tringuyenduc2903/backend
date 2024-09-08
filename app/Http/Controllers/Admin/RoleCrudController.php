<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EmployeePermissionEnum;
use App\Http\Requests\Admin\RoleRequest;
use App\Models\Permission;
use App\Models\Role;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\Pro\Http\Controllers\Operations\FetchOperation;
use Backpack\Pro\Http\Controllers\Operations\InlineCreateOperation;

/**
 * Class RoleCrudController
 *
 * @property-read CrudPanel $crud
 */
class RoleCrudController extends CrudController
{
    use CreateOperation;
    use FetchOperation;
    use InlineCreateOperation;
    use ListOperation;
    use UpdateOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     */
    public function setup(): void
    {
        CRUD::setModel(Role::class);
        CRUD::setRoute(route('roles.index'));
        CRUD::setEntityNameStrings(trans('Role'), trans('Roles'));

        deny_access(EmployeePermissionEnum::ROLE_CRUD);
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
        CRUD::addColumn([
            'name' => 'users',
            'label' => trans('Employees'),
            'type' => 'relationship_count',
            'wrapper' => [
                'href' => fn ($_, $__, $entry): string => backpack_url(sprintf(
                    'employees?role_id=%s&role_id_text=%s',
                    $entry->getKey(),
                    $entry->name
                )),
            ],
            'suffix' => ' '.trans('employee(s)'),
        ]);
        CRUD::column('permissions')
            ->label(trans('Permissions'));
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
        CRUD::setValidation(RoleRequest::class);

        CRUD::field('name')
            ->label(trans('Name'));
        CRUD::addField([
            'name' => 'permissions',
            'label' => trans('Permissions'),
            'data_source' => route('roles.fetchPermissions'),
            'minimum_input_length' => 0,
        ]);
    }

    protected function fetchPermissions()
    {
        return $this->fetch(Permission::class);
    }
}
