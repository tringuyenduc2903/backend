<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EmployeePermission;
use App\Http\Requests\Admin\SettingRequest;
use App\Models\Branch;
use App\Models\Setting;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\Pro\Http\Controllers\Operations\FetchOperation;

/**
 * Class SettingCrudController
 *
 * @property-read CrudPanel $crud
 */
class SettingCrudController extends CrudController
{
    use FetchOperation;
    use ListOperation;
    use UpdateOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     */
    public function setup(): void
    {
        CRUD::setModel(Setting::class);
        CRUD::setRoute(route('settings.index'));
        CRUD::setEntityNameStrings(trans('Setting'), trans('Settings'));

        deny_access(EmployeePermission::SETTING_CRUD);
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     */
    protected function setupListOperation(): void
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
     */
    protected function setupUpdateOperation(): void
    {
        /** @var Setting $setting */
        $setting = CRUD::getCurrentEntry();

        CRUD::setValidation(SettingRequest::class);
        CRUD::setValidation($setting->validation_rules);

        CRUD::field('active')
            ->label(trans('Active'))
            ->type('switch');
        CRUD::addFields($setting->fields);

        $value = CRUD::getCurrentEntry()->getAttribute('name');

        CRUD::setTitle($value);
        CRUD::setHeading($value);
    }

    protected function fetchBranches()
    {
        return $this->fetch(Branch::class);
    }
}
