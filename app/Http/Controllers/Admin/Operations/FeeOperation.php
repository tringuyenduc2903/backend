<?php

namespace App\Http\Controllers\Admin\Operations;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\Route;

trait FeeOperation
{
    /**
     * Define which routes are needed for this operation.
     *
     * @param  string  $segment  Name of the current entity (singular). Used as first URL segment.
     * @param  string  $routeName  Prefix of the route name.
     * @param  string  $controller  Name of the current CrudController.
     */
    protected function setupFeeRoutes(string $segment, string $routeName, string $controller)
    {
        Route::post($segment.'/fee', [
            'as' => $routeName.'.fee',
            'uses' => $controller.'@fee',
            'operation' => 'fee',
        ]);
    }

    /**
     * Add the default settings, buttons, etc that this operation needs.
     */
    protected function setupFeeDefaults()
    {
        CRUD::allowAccess('fee');
    }
}
