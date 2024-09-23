<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EmployeePermissionEnum;
use App\Enums\OptionStatus;
use App\Enums\OptionType;
use App\Enums\ProductType;
use App\Enums\ProductVisibility;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\app\Library\Widget;
use Backpack\Pro\Http\Controllers\Operations\BulkTrashOperation as V2;
use Backpack\Pro\Http\Controllers\Operations\DropzoneOperation;
use Backpack\Pro\Http\Controllers\Operations\FetchOperation;
use Backpack\Pro\Http\Controllers\Operations\TrashOperation as V1;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class ProductCrudController
 *
 * @property-read CrudPanel $crud
 */
class ProductCrudController extends CrudController
{
    use CreateOperation;
    use DropzoneOperation;
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
        CRUD::column('categories')
            ->label(trans('Categories'));
        CRUD::column('published')
            ->label(trans('Publish'))
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
        CRUD::column('manufacturer')
            ->label(trans('Manufacturer'));

        CRUD::filter('name')
            ->label(trans('Name'))
            ->type('text');
        CRUD::addFilter(
            [
                'name' => 'category_id',
                'label' => trans('Categories'),
                'type' => 'select2_ajax',
                'minimum_input_length' => 0,
                'method' => 'POST',
            ],
            route('products.fetchCategories'),
            fn (string $values) => CRUD::addClause(
                'whereHas',
                'categories',
                fn (Builder $query) => $query->where('category_id', json_decode($values))
            )
        );
        CRUD::filter('draft')
            ->label(trans('Draft'))
            ->type('simple')
            ->whenActive(fn () => CRUD::addClause('wherePublished', false))
            ->whenInactive(fn () => CRUD::addClause('wherePublished', true));
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
        $this->setupCreateOperation();

        set_title();
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     */
    protected function setupCreateOperation(): void
    {
        CRUD::setValidation(ProductRequest::class);

        Widget::add([
            'type' => 'script',
            'content' => resource_path('assets/js/admin/forms/product.js'),
        ]);

        $code = current_currency();

        CRUD::field('published')
            ->label(trans('Publish'))
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
            'name' => 'categories',
            'label' => trans('Categories'),
            'inline_create' => [
                'create_route' => route('categories-inline-create-save'),
                'modal_route' => route('categories-inline-create'),
            ],
            'data_source' => route('products.fetchCategories'),
            'minimum_input_length' => 0,
            'tab' => trans('Basic information'),
        ]);
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
            'max_rows' => 40,
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
            'name' => 'seo',
            'label' => trans('SEO'),
            'subfields' => [[
                'name' => 'title',
                'label' => trans('Title'),
            ], [
                'name' => 'description',
                'label' => trans('Description'),
                'type' => 'textarea',
            ], [
                'name' => 'image',
                'label' => trans('Image'),
                'type' => 'image',
                'crop' => true,
                'withFiles' => [
                    'disk' => 'product',
                ],
            ], [
                'name' => 'author',
                'label' => trans('Author'),
            ], [
                'name' => 'robots',
                'label' => trans('Robot tags'),
                'type' => 'table',
                'columns' => [
                    'name' => trans('Name'),
                    'value' => trans('Value'),
                ],
                'max' => 5,
            ]],
            'tab' => trans('Basic information'),
        ]);
        CRUD::field('options')
            ->label(trans('Options'))
            ->subfields([[
                'name' => 'sku',
                'label' => trans('SKU'),
            ], [
                'name' => 'price',
                'label' => trans('Price'),
                'type' => 'number',
                'prefix' => $code.' ',
                'default' => 0,
                'wrapper' => [
                    'class' => 'form-group col-sm-12 col-md-6',
                ],
            ], [
                'name' => 'preview',
                'label' => trans('Price preview'),
                'prefix' => $code.' ',
                'attributes' => [
                    'disabled' => true,
                ],
                'wrapper' => [
                    'class' => 'form-group col-sm-12 col-md-6',
                ],
            ], [
                'name' => 'value_added_tax',
                'label' => trans('Value added tax'),
                'type' => 'number',
                'prefix' => '%',
                'default' => 10,
            ], [
                'name' => 'images',
                'label' => trans('Images'),
                'type' => 'dropzone',
                'withFiles' => [
                    'disk' => 'product',
                ],
            ], [
                'name' => 'color',
                'label' => trans('Color'),
                'wrapper' => [
                    'class' => 'form-group col-sm-12 col-md-6 col-xl-4',
                ],
            ], [
                'name' => 'version',
                'label' => trans('Version'),
                'wrapper' => [
                    'class' => 'form-group col-sm-12 col-md-6 col-xl-4',
                ],
            ], [
                'name' => 'volume',
                'label' => trans('Volume'),
                'wrapper' => [
                    'class' => 'form-group col-sm-12 col-md-6 col-xl-4',
                ],
            ], [
                'name' => 'type',
                'label' => trans('Type'),
                'type' => 'select2_from_array',
                'options' => OptionType::values(),
                'allows_null' => false,
            ], [
                'name' => 'quantity',
                'label' => trans('Quantity'),
                'type' => 'number',
                'wrapper' => [
                    'class' => 'form-group col-sm-12 col-md-6',
                ],
            ], [
                'name' => 'status',
                'label' => trans('Status'),
                'type' => 'select2_from_array',
                'options' => OptionStatus::values(),
                'allows_null' => false,
                'wrapper' => [
                    'class' => 'form-group col-sm-12 col-md-6',
                ],
            ], [
                'name' => 'weight',
                'label' => trans('Weight'),
                'type' => 'number',
                'prefix' => 'gram',
            ], [
                'name' => 'length',
                'label' => trans('Length'),
                'type' => 'number',
                'prefix' => 'cm',
                'wrapper' => [
                    'class' => 'form-group col-sm-12 col-md-6 col-xl-4',
                ],
            ], [
                'name' => 'width',
                'label' => trans('Width'),
                'type' => 'number',
                'prefix' => 'cm',
                'wrapper' => [
                    'class' => 'form-group col-sm-12 col-md-6 col-xl-4',
                ],
            ], [
                'name' => 'height',
                'label' => trans('Height'),
                'type' => 'number',
                'prefix' => 'cm',
                'wrapper' => [
                    'class' => 'form-group col-sm-12 col-md-6 col-xl-4',
                ],
            ], [
                'name' => 'specifications',
                'label' => trans('Specifications'),
                'type' => 'table',
                'columns' => [
                    'title' => trans('Title'),
                    'description' => trans('Description'),
                ],
                'max' => 40,
            ]])
            ->tab(trans('Options'));
        CRUD::addField([
            'name' => 'upsell',
            'label' => trans('Upsell products'),
            'data_source' => route('products.fetchProducts'),
            'minimum_input_length' => 0,
            'tab' => trans('Lists'),
        ]);
        CRUD::addField([
            'name' => 'cross_sell',
            'label' => trans('Cross-sell products'),
            'data_source' => route('products.fetchProducts'),
            'minimum_input_length' => 0,
            'tab' => trans('Lists'),
        ]);
        CRUD::addField([
            'name' => 'related_products',
            'label' => trans('Related products'),
            'data_source' => route('products.fetchProducts'),
            'minimum_input_length' => 0,
            'tab' => trans('Lists'),
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
                'youtube_api_key' => config('services.youtube.key'),
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

    protected function fetchCategories()
    {
        return $this->fetch(Category::class);
    }

    protected function fetchProducts()
    {
        return $this->fetch([
            'model' => Product::class,
            'query' => function (Product $product): Product|Builder {
                $form = collect(request('form'))->pluck('value', 'name');

                return isset($form['id'])
                    ? $product->whereNot('id', $form['id'])
                    : $product;
            },
        ]);
    }
}
