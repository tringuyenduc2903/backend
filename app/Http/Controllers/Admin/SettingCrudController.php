<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EmployeePermissionEnum;
use App\Http\Requests\Admin\SettingRequest;
use App\Models\Setting;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class SettingCrudController
 *
 * @property-read CrudPanel $crud
 */
class SettingCrudController extends CrudController
{
    use ListOperation;
    use UpdateOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(Setting::class);
        CRUD::setRoute(route('settings.index'));
        CRUD::setEntityNameStrings(trans('Setting'), trans('Settings'));

        deny_access(EmployeePermissionEnum::SETTING_CRUD);
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
        CRUD::column('key')
            ->label(trans('Key'));
        CRUD::column('name')
            ->label(trans('Name'));
        CRUD::column('active')
            ->label(trans('Active'))
            ->type('switch');

        CRUD::filter('type')
            ->label(trans('Type'))
            ->type('select2')
            ->values([
                'homepage' => trans('Home page'),
                'header' => trans('Header'),
                'footer' => trans('Footer'),
                'auth' => trans('Auth'),
                'store' => trans('Store'),
            ])
            ->whenActive(fn (string $value) => CRUD::addClause('where', 'key', 'like', "{$value}_%"));
        CRUD::filter('inactive')
            ->label(trans('Inactive'))
            ->type('simple')
            ->whenActive(fn () => CRUD::addClause('whereActive', false))
            ->whenInactive(fn () => CRUD::addClause('whereActive', true));
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
        $setting = CRUD::getCurrentEntry();

        CRUD::setValidation(SettingRequest::class);
        CRUD::setValidation(json_decode(
            $setting->getAttribute('validation_rules'),
            true
        ));

        CRUD::field('name')
            ->label(trans('Name'))
            ->attributes([
                'disabled' => true,
            ]);
        CRUD::field('active')
            ->label(trans('Active'))
            ->type('switch');
        CRUD::addFields(json_decode(
            $setting->getAttribute('fields'),
            true
        ));
    }
}
