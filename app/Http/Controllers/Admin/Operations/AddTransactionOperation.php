<?php

namespace App\Http\Controllers\Admin\Operations;

use Alert;
use App\Enums\OrderTransactionStatus;
use App\Http\Requests\Admin\Operations\AddTransactionRequest;
use App\Models\Order;
use App\Models\OrderMotorcycle;
use App\Models\OrderTransaction;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\app\Library\Widget;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

trait AddTransactionOperation
{
    /**
     * Define which routes are needed for this operation.
     *
     * @param  string  $segment  Name of the current entity (singular). Used as first URL segment.
     * @param  string  $routeName  Prefix of the route name.
     * @param  string  $controller  Name of the current CrudController.
     */
    protected function setupAddTransactionRoutes(string $segment, string $routeName, string $controller)
    {
        Route::get($segment.'/{id}/add-transaction', [
            'as' => $routeName.'.addTransaction',
            'uses' => $controller.'@addTransaction',
            'operation' => 'add_transaction',
        ]);

        Route::post($segment.'/{id}/add-transaction', [
            'as' => $routeName.'.storeTransaction',
            'uses' => $controller.'@storeTransaction',
            'operation' => 'add_transaction',
        ]);
    }

    /**
     * Add the default settings, buttons, etc that this operation needs.
     */
    protected function setupAddTransactionDefaults()
    {
        CRUD::setAccessCondition(
            'add_transaction',
            fn (Order|OrderMotorcycle $entry): bool => $entry->canAddTransaction()
        );

        CRUD::operation('add_transaction', function () {
            CRUD::loadDefaultOperationSettingsFromConfig();
            CRUD::setupDefaultSaveActions();
        });

        CRUD::operation(
            ['list', 'show'],
            fn () => CRUD::addButton('line', 'add_transaction', 'view', 'crud.buttons.transaction.add_transaction')
        );
    }

    protected function setupAddTransactionOperation()
    {
        CRUD::setValidation(AddTransactionRequest::class);

        CRUD::setTitle(trans('Add cash transactions'));
        CRUD::setSubheading(trans('Add cash transactions'));

        Widget::add([
            'type' => 'script',
            'content' => resource_path('assets/js/admin/forms/transaction.js'),
        ]);

        $code = current_currency();

        CRUD::field('amount')
            ->label(trans('Amount (Money)'))
            ->type('number')
            ->default(CRUD::getCurrentEntry()->getAttribute('to_be_paid'))
            ->prefix($code);
        CRUD::field('preview')
            ->label(trans('Price preview'))
            ->prefix($code)
            ->attributes([
                'disabled' => true,
            ]);
        CRUD::field('reference')
            ->label(trans('Reference'));

        OrderTransaction::creating(function (OrderTransaction $order_transaction) {
            $order_transaction->status = OrderTransactionStatus::PAID;
        });
    }

    protected function addTransaction(string $id): View
    {
        CRUD::hasAccessOrFail('add_transaction');

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

        return view('crud.transaction.add_transaction', $this->data);
    }

    protected function storeTransaction(): RedirectResponse
    {
        CRUD::hasAccessOrFail('add_transaction');

        /** @var AddTransactionRequest $request */
        // execute the FormRequest authorization and validation, if one is required
        $request = CRUD::validateRequest();

        // register any Model Events defined on fields
        CRUD::registerFieldEvents();

        /** @var Order $order */
        $order = CRUD::getCurrentEntry();

        // update the row in the db
        $item = $order
            ->transactions()
            ->create($request->validated());

        $this->data['entry'] = $this->crud->entry = $item;

        // show a success message
        Alert::success(trans('backpack::crud.insert_success'))->flash();

        // save the redirect choice for next time
        CRUD::setSaveAction();

        return CRUD::performSaveAction($item->getKey());
    }
}
