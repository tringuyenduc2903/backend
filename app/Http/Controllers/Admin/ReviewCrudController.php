<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EmployeePermissionEnum;
use App\Http\Requests\Admin\ReviewRequest;
use App\Models\Review;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\Pro\Http\Controllers\Operations\DropzoneOperation;
use Backpack\Pro\Http\Controllers\Operations\FetchOperation;

/**
 * Class ReviewCrudController
 *
 * @property-read CrudPanel $crud
 */
class ReviewCrudController extends CrudController
{
    use DeleteOperation;
    use DropzoneOperation;
    use FetchOperation;
    use ListOperation;
    use ShowOperation;
    use UpdateOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     */
    public function setup(): void
    {
        CRUD::setModel(Review::class);
        CRUD::setRoute(route('reviews.index'));
        CRUD::setEntityNameStrings(trans('Review'), trans('Reviews'));

        deny_access(EmployeePermissionEnum::REVIEW_CRUD);
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     */
    protected function setupListOperation(): void
    {
        CRUD::column('content')
            ->label(trans('Content'));
        CRUD::addColumn([
            'name' => 'rate',
            'label' => trans('Rate'),
            'type' => 'number',
            'suffix' => ' ★',
        ]);
        CRUD::column('parent.sku')
            ->label(trans('Product'));
        CRUD::column('reviewable.name')
            ->label(trans('Customer'));
    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     */
    protected function setupUpdateOperation(): void
    {
        CRUD::setValidation(ReviewRequest::class);
    }
}
