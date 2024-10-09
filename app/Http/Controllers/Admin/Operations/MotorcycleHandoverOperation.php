<?php

namespace App\Http\Controllers\Admin\Operations;

use Alert;
use App\Enums\OrderStatus;
use App\Http\Requests\Admin\Operations\MotorcycleHandoverRequest;
use App\Models\Employee;
use App\Models\MotorCycle;
use App\Models\OrderMotorcycle;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\Pro\Http\Controllers\Operations\FetchOperation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

trait MotorcycleHandoverOperation
{
    use FetchOperation;

    /**
     * Define which routes are needed for this operation.
     *
     * @param  string  $segment  Name of the current entity (singular). Used as first URL segment.
     * @param  string  $routeName  Prefix of the route name.
     * @param  string  $controller  Name of the current CrudController.
     */
    protected function setupMotorcycleHandoverRoutes(string $segment, string $routeName, string $controller)
    {
        Route::get($segment.'/{id}/motorcycle-handover', [
            'as' => $routeName.'.addMotorcycleHandover',
            'uses' => $controller.'@addMotorcycleHandover',
            'operation' => 'motorcycle_handover',
        ]);

        Route::post($segment.'/{id}/motorcycle-handover', [
            'as' => $routeName.'.storeMotorcycleHandover',
            'uses' => $controller.'@storeMotorcycleHandover',
            'operation' => 'motorcycle_handover',
        ]);
    }

    /**
     * Add the default settings, buttons, etc that this operation needs.
     */
    protected function setupMotorcycleHandoverDefaults()
    {
        CRUD::setAccessCondition(
            'motorcycle_handover',
            fn (OrderMotorcycle $entry): bool => $entry->canMotorcycleHandover()
        );

        CRUD::operation('motorcycle_handover', function () {
            CRUD::loadDefaultOperationSettingsFromConfig();
            CRUD::setupDefaultSaveActions();
        });

        CRUD::operation(
            ['list', 'show'],
            fn () => CRUD::addButton('line', 'motorcycle_handover', 'view', 'crud.buttons.shipment.motorcycle_handover', 'end'));
    }

    protected function setupMotorcycleHandoverOperation()
    {
        CRUD::setValidation(MotorcycleHandoverRequest::class);

        CRUD::setTitle(trans('Motorcycle handover'));
        CRUD::setSubheading(trans('Motorcycle handover'));

        CRUD::field('id')
            ->type('hidden')
            ->default(CRUD::getCurrentEntry()->getAttribute('id'));
        CRUD::addField([
            'name' => 'motor_cycle',
            'label' => trans('Motor cycle'),
            'type' => 'select2_from_ajax',
            'model' => MotorCycle::class,
            'data_source' => CRUD::getRoute().'/fetch/motorcycles',
            'minimum_input_length' => 0,
            'method' => 'POST',
            'attribute' => 'chassis_number',
            'include_all_form_fields' => true,
        ]);
    }

    protected function addMotorcycleHandover(string $id): View
    {
        CRUD::hasAccessOrFail('motorcycle_handover');

        // get entry ID from Request (makes sure its the last ID for nested resources)
        $id = CRUD::getCurrentEntryId() ?? $id;

        // register any Model Events defined on fields
        CRUD::registerFieldEvents();

        // get the info for that entry
        $this->data['entry'] = CRUD::getEntryWithLocale($id);

        // prepare the fields you need to show
        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = CRUD::getSaveAction();
        $this->data['title'] = CRUD::getTitle() ?? trans('backpack::crud.edit').' '.$this->crud->entity_name;
        $this->data['id'] = $id;

        return view('crud.shipment.motorcycle_handover', $this->data);
    }

    protected function storeMotorcycleHandover(): RedirectResponse
    {
        CRUD::hasAccessOrFail('motorcycle_handover');

        /** @var MotorcycleHandoverRequest $request */
        // execute the FormRequest authorization and validation, if one is required
        $request = CRUD::validateRequest();

        // register any Model Events defined on fields
        CRUD::registerFieldEvents();

        /** @var OrderMotorcycle $order */
        $order = CRUD::getCurrentEntry();

        // update the row in the db
        $order
            ->motor_cycle()
            ->associate(MotorCycle::findOrFail($request->validated('motorcycle')))
            ->save();
        $order
            ->forceFill([
                'status' => OrderStatus::COMPLETED,
            ])
            ->save();

        // show a success message
        Alert::success(trans('backpack::crud.insert_success'))->flash();

        // save the redirect choice for next time
        CRUD::setSaveAction();

        return redirect(CRUD::getRoute());
    }

    protected function fetchMotorcycles()
    {
        return $this->fetch([
            'model' => MotorCycle::class,
            'query' => function (MotorCycle $motorcycle): MotorCycle|Builder {
                $form = collect(request('form'))->pluck('value', 'name');

                /** @var Employee $employee */
                $employee = backpack_user();
                $order = OrderMotorcycle::findOrFail($form['id']);

                return $motorcycle
                    ->whereBranchId($employee->branch->id)
                    ->whereOptionId($order->option->id);
            },
        ]);
    }
}
