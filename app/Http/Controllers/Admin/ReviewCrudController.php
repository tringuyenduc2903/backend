<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EmployeePermissionEnum;
use App\Http\Requests\Admin\ReviewRequest;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Option;
use App\Models\Review;
use Backpack\CRUD\app\Http\Controllers\CrudController;
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
    use DropzoneOperation;
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

        CRUD::operation(
            'list',
            fn () => CRUD::addButton('line', 'show', 'view', 'crud.buttons.review.show', 'beginning'));
        CRUD::operation(
            ['list', 'show'],
            fn () => CRUD::addButton('line', 'update', 'view', 'crud.buttons.review.update', 'end'));

        deny_access(EmployeePermissionEnum::REVIEW_CRUD);
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     */
    protected function setupListOperation(): void
    {
        CRUD::column('parent.sku')
            ->label(trans('Product'));
        CRUD::column('customer')
            ->label(trans('Customer'));
        CRUD::column('content')
            ->label(trans('Content'));
        CRUD::addColumn([
            'name' => 'rate',
            'label' => trans('Rate'),
            'type' => 'number',
            'suffix' => ' ★',
        ]);
        CRUD::column('images')
            ->label(trans('Images'))
            ->type('dropzone')
            ->withFiles(['disk' => 'review']);

        CRUD::addFilter(
            [
                'name' => 'parent_id',
                'label' => trans('Product'),
                'type' => 'select2_ajax',
                'minimum_input_length' => 0,
                'method' => 'POST',
                'select_attribute' => 'sku',
            ],
            route('reviews.fetchOptions'),
            function (int $sku) {
                CRUD::addClause('whereParentId', $sku);
                CRUD::addClause('whereParentType', Option::class);
            },
            fn () => CRUD::addClause('whereParentType', Option::class)
        );
        CRUD::addFilter(
            [
                'name' => 'reviewable_id',
                'label' => trans('Customer'),
                'type' => 'select2_ajax',
                'minimum_input_length' => 0,
                'method' => 'POST',
            ],
            route('reviews.fetchCustomers'),
            function (int $sku) {
                CRUD::addClause('whereReviewableId', $sku);
                CRUD::addClause('whereReviewableType', Customer::class);
            },
            fn () => CRUD::addClause('whereReviewableType', Customer::class)
        );
    }

    /**
     * Define what happens when the Show operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     */
    protected function setupShowOperation(): void
    {
        CRUD::setSubheading(trans('See details'));

        CRUD::column('content')
            ->label(trans('Content'))
            ->type('textarea');
        CRUD::addColumn([
            'name' => 'rate',
            'label' => trans('Rate'),
            'type' => 'number',
            'suffix' => ' ★',
        ]);
        CRUD::column('images')
            ->label(trans('Images'))
            ->type('dropzone')
            ->withFiles(['disk' => 'review']);
    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     */
    protected function setupUpdateOperation(): void
    {
        CRUD::setSubheading(trans('Reply this review'));

        CRUD::setValidation(ReviewRequest::class);

        CRUD::field('reply')
            ->label(trans('Reply'))
            ->subfields([[
                'name' => 'content',
                'label' => trans('Content'),
                'type' => 'textarea',
            ], [
                'name' => 'images',
                'label' => trans('Images'),
                'type' => 'dropzone',
                'withFiles' => [
                    'disk' => 'review',
                ],
            ]]);

        Review::saving(function (Review $review) {
            /** @var Employee $employee */
            $employee = backpack_user();

            $review->reviewable_id = $employee->id;
            $review->reviewable_type = Employee::class;
        });
    }

    protected function fetchOptions()
    {
        return $this->fetch([
            'model' => Option::class,
            'searchable_attributes' => ['sku'],
        ]);
    }

    protected function fetchCustomers()
    {
        return $this->fetch(Customer::class);
    }
}
