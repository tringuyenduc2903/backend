<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EmployeePermission;
use App\Http\Requests\Admin\BranchRequest;
use App\Models\Branch;
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
use Backpack\Pro\Http\Controllers\Operations\InlineCreateOperation;
use Backpack\Pro\Http\Controllers\Operations\TrashOperation as V1;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class BranchCrudController
 *
 * @property-read CrudPanel $crud
 */
class BranchCrudController extends CrudController
{
    use CreateOperation;
    use FetchOperation;
    use InlineCreateOperation;
    use ListOperation;
    use UpdateOperation;
    use V1;
    use V2;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     */
    public function setup(): void
    {
        CRUD::setModel(Branch::class);
        CRUD::setRoute(route('branches.index'));
        CRUD::setEntityNameStrings(trans('Branch'), trans('Branches'));

        deny_access(EmployeePermission::BRANCH_CRUD);
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
        CRUD::column('phone_number')
            ->label(trans('Phone number'))
            ->type('phone');
        CRUD::column('image')
            ->label(trans('Image'))
            ->type('image')
            ->withFiles(['disk' => 'branch']);
        CRUD::column('address_preview')
            ->label(trans('Address'));
    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     */
    protected function setupUpdateOperation(): void
    {
        $this->setupCreateOperation();

        $value = CRUD::getCurrentEntry()->getAttribute('name');

        CRUD::setTitle($value);
        CRUD::setHeading($value);
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     */
    protected function setupCreateOperation(): void
    {
        CRUD::setValidation(BranchRequest::class);

        CRUD::field('name')
            ->label(trans('Name'));
        CRUD::field('phone_number')
            ->label(trans('Phone number'));
        CRUD::addField([
            'name' => 'image',
            'label' => trans('Image'),
            'type' => 'image',
            'crop' => true,
            'withFiles' => [
                'disk' => 'branch',
            ],
        ]);
        CRUD::field('alt')
            ->label(trans('Alt text'));
        CRUD::field('country')
            ->label(trans('Country'))
            ->default('Việt Nam');
        CRUD::addField([
            'name' => 'province',
            'label' => trans('Province'),
            'data_source' => route('branches.fetchProvinces'),
            'minimum_input_length' => 0,
            'method' => 'POST',
        ]);
        CRUD::addField([
            'name' => 'district',
            'label' => trans('District'),
            'data_source' => route('branches.fetchDistricts'),
            'minimum_input_length' => 0,
            'method' => 'POST',
            'dependencies' => 'province',
            'include_all_form_fields' => true,
        ]);
        CRUD::addField([
            'name' => 'ward',
            'label' => trans('Ward'),
            'data_source' => route('branches.fetchWards'),
            'minimum_input_length' => 0,
            'method' => 'POST',
            'dependencies' => ['province', 'district'],
            'include_all_form_fields' => true,
        ]);
        CRUD::field('address_detail')
            ->label(trans('Address detail'))
            ->type('textarea');
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

                return isset($form['province'])
                    ? $district->whereProvinceId($form['province'])
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

                return isset($form['district'])
                    ? $ward->whereDistrictId($form['district'])
                    : $ward;
            },
        ]);
    }
}
