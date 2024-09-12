<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EmployeePermissionEnum;
use App\Enums\MotorCycleStatus;
use App\Enums\ProductType;
use App\Http\Requests\Admin\MotorCycleRequest;
use App\Models\Branch;
use App\Models\MotorCycle;
use App\Models\Option;
use App\Models\Product;
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
 * Class MotorCycleCrudController
 *
 * @property-read CrudPanel $crud
 */
class MotorCycleCrudController extends CrudController
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
        CRUD::setModel(MotorCycle::class);
        CRUD::setRoute(route('motor-cycles.index'));
        CRUD::setEntityNameStrings(trans('Motor cycle'), trans('Motor cycles'));

        deny_access(EmployeePermissionEnum::MOTOR_CYCLE_CRUD);
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     */
    protected function setupListOperation(): void
    {
        CRUD::column('chassis_number')
            ->label(trans('Chassis number'));
        CRUD::column('engine_number')
            ->label(trans('Engine number'));
        CRUD::addColumn([
            'name' => 'status',
            'label' => trans('Status'),
            'type' => 'select2_from_array',
            'options' => MotorCycleStatus::values(),
        ]);
        CRUD::column('branch')
            ->label(trans('Branch'));
        CRUD::column('option.sku')
            ->label(trans('Product'));

        CRUD::filter('chassis_number')
            ->label(trans('Chassis number'))
            ->type('text');
        CRUD::filter('engine_number')
            ->label(trans('Engine number'))
            ->type('text');
        CRUD::filter('status')
            ->label(trans('Status'))
            ->type('dropdown')
            ->values(MotorCycleStatus::values());
        CRUD::addFilter(
            [
                'name' => 'branch_id',
                'label' => trans('Branch'),
                'type' => 'select2_ajax',
                'minimum_input_length' => 0,
                'method' => 'POST',
            ],
            route('motor-cycles.fetchBranches')
        );
        CRUD::addFilter(
            [
                'name' => 'option_id',
                'label' => trans('Product'),
                'type' => 'select2_ajax',
                'minimum_input_length' => 0,
                'method' => 'POST',
                'select_attribute' => 'sku',
            ],
            route('motor-cycles.fetchOptions')
        );
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
        CRUD::setValidation(MotorCycleRequest::class);

        CRUD::field('chassis_number')
            ->label(trans('Chassis number'));
        CRUD::field('engine_number')
            ->label(trans('Engine number'));
        CRUD::addField([
            'name' => 'option',
            'label' => trans('Product'),
            'data_source' => route('motor-cycles.fetchOptions'),
            'minimum_input_length' => 0,
            'attribute' => 'sku',
        ]);
    }

    protected function fetchBranches()
    {
        return $this->fetch(Branch::class);
    }

    protected function fetchOptions()
    {
        return $this->fetch([
            'model' => Option::class,
            'query' => function (Option $option): Builder|Option {
                return $option->whereHas(
                    'product',
                    function (Builder $query) {
                        /** @var Product $query */
                        return $query->whereType(ProductType::MOTOR_CYCLE);
                    }
                );
            },
            'searchable_attributes' => ['sku'],
        ]);
    }
}
