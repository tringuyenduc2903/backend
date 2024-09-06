<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EmployeePermissionEnum;
use App\Enums\ProductType;
use App\Enums\ProductVisibility;
use App\Http\Requests\Admin\ProductStoreRequest;
use App\Http\Requests\Admin\ProductUpdateRequest;
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

/**
 * Class ProductCrudController
 *
 * @property-read CrudPanel $crud
 */
class ProductCrudController extends CrudController
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
        CRUD::setModel(Product::class);
        CRUD::setRoute(route('products.index'));
        CRUD::setEntityNameStrings(trans('Product'), trans('Products'));

        deny_access(EmployeePermissionEnum::PRODUCT_CRUD);
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
        CRUD::column('enabled')
            ->label(trans('Enabled'))
            ->type('switch');
        CRUD::addColumn([
            'name' => 'visibility',
            'label' => trans('Visibility'),
            'type' => 'select2_from_array',
            'options' => ProductVisibility::values(),
        ]);
        CRUD::addColumn([
            'name' => 'type',
            'label' => trans('Type'),
            'type' => 'select2_from_array',
            'options' => ProductType::values(),
        ]);
        CRUD::column('manufacturer')
            ->label(trans('Manufacturer'));
        CRUD::column('search_url')
            ->label(trans('Search URL'));

        CRUD::filter('name')
            ->label(trans('Name'))
            ->type('text');
        CRUD::filter('disabled')
            ->label(trans('Disabled'))
            ->type('simple')
            ->whenActive(fn () => CRUD::addClause('whereEnabled', false))
            ->whenInactive(fn () => CRUD::addClause('whereEnabled', true));
        CRUD::filter('visibility')
            ->label(trans('Visibility'))
            ->type('dropdown')
            ->options(ProductVisibility::values());
        CRUD::filter('type')
            ->label(trans('Type'))
            ->type('dropdown')
            ->values(ProductType::values());
        CRUD::filter('manufacturer')
            ->label(trans('Manufacturer'))
            ->type('text');
    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     */
    protected function setupUpdateOperation(): void
    {
        CRUD::setValidation(ProductUpdateRequest::class);

        $this->productFields();
    }

    protected function productFields(): void
    {
        CRUD::field('enabled')
            ->label(trans('Enabled'))
            ->type('switch')
            ->default(true)
            ->tab(trans('Basic information'));
        CRUD::field('name')
            ->label(trans('Name'))
            ->tab(trans('Basic information'));
        CRUD::field('description')
            ->label(trans('Description'))
            ->type('tinymce')
            ->tab(trans('Basic information'));
        CRUD::addField([
            'name' => 'visibility',
            'label' => trans('Visibility'),
            'type' => 'select2_from_array',
            'options' => ProductVisibility::values(),
            'default' => ProductVisibility::CATALOG_AND_SEARCH,
            'tab' => trans('Basic information'),
        ]);
        CRUD::addField([
            'name' => 'type',
            'label' => trans('Type'),
            'type' => 'select2_from_array',
            'options' => ProductType::values(),
            'default' => ProductType::MOTOR_CYCLE,
            'tab' => trans('Basic information'),
        ]);
        CRUD::field('manufacturer')
            ->label(trans('Manufacturer'))
            ->tab(trans('Basic information'));
        CRUD::addField([
            'name' => 'specifications',
            'label' => trans('Specifications'),
            'type' => 'repeatable',
            'subfields' => [[
                'name' => 'title',
                'label' => trans('Title'),
                'wrapper' => [
                    'class' => 'form-group col-sm-12 col-md-5',
                ],
            ], [
                'name' => 'description',
                'label' => trans('Description'),
                'wrapper' => [
                    'class' => 'form-group col-sm-12 col-md-7',
                ],
            ]],
            'max_rows' => 30,
            'tab' => trans('Basic information'),
        ]);
        CRUD::addField([
            'name' => 'search_url',
            'label' => trans('Search URL'),
            'type' => 'slug',
            'target' => 'name',
            'tab' => trans('Basic information'),
        ]);
        CRUD::addField([
            'name' => 'images',
            'label' => trans('Images'),
            'type' => 'repeatable',
            'subfields' => [[
                'name' => 'image',
                'label' => trans('Image'),
                'type' => 'image',
                'crop' => true,
                'withFiles' => [
                    'disk' => 'product',
                ],
            ], [
                'name' => 'alt',
                'label' => trans('Alt text'),
            ]],
            'max_rows' => 15,
            'tab' => trans('Media'),
        ]);
        CRUD::addField([
            'name' => 'videos',
            'label' => trans('Videos'),
            'type' => 'repeatable',
            'subfields' => [[
                'name' => 'video',
                'label' => trans('Video'),
                'type' => 'video',
                'youtube_api_key' => config('youtube.key'),
            ], [
                'name' => 'image',
                'label' => trans('Image'),
                'type' => 'image',
                'crop' => true,
                'withFiles' => [
                    'disk' => 'product',
                ],
                'wrapper' => [
                    'class' => 'form-group col-sm-12 col-lg-4',
                ],
            ], [
                'name' => 'title',
                'label' => trans('Title'),
                'wrapper' => [
                    'class' => 'form-group col-sm-12 col-lg-4',
                ],
            ], [
                'name' => 'description',
                'label' => trans('Description'),
                'type' => 'textarea',
                'wrapper' => [
                    'class' => 'form-group col-sm-12 col-lg-4',
                ],
            ]],
            'max_rows' => 1,
            'tab' => trans('Media'),
        ]);
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     */
    protected function setupCreateOperation(): void
    {
        CRUD::setValidation(ProductStoreRequest::class);

        $this->productFields();
    }

    protected function fetchProducts()
    {
        return $this->fetch(Product::class);
    }
}
