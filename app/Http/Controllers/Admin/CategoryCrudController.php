<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EmployeePermission;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\Pro\Http\Controllers\Operations\BulkTrashOperation as V2;
use Backpack\Pro\Http\Controllers\Operations\InlineCreateOperation;
use Backpack\Pro\Http\Controllers\Operations\TrashOperation as V1;

/**
 * Class CategoryCrudController
 *
 * @property-read CrudPanel $crud
 */
class CategoryCrudController extends CrudController
{
    use CreateOperation;
    use InlineCreateOperation;
    use ListOperation;
    use UpdateOperation;
    use V1;
    use V2;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     */
    public function setup(): void
    {
        CRUD::setModel(Category::class);
        CRUD::setRoute(route('categories.index'));
        CRUD::setEntityNameStrings(trans('Category'), trans('Categories'));

        deny_access(EmployeePermission::CATEGORY_CRUD);
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
        CRUD::column('image')
            ->label(trans('Image'))
            ->type('image')
            ->withFiles(['disk' => 'category']);
        CRUD::column('search_url')
            ->label(trans('Search URL'));
        CRUD::addColumn([
            'name' => 'products',
            'label' => trans('Products'),
            'type' => 'relationship_count',
            'wrapper' => [
                'href' => fn ($_, $__, $entry): string => backpack_url(sprintf(
                    'products?category_id=%s&category_id_text=%s',
                    $entry->getKey(),
                    $entry->name
                )),
            ],
            'suffix' => ' '.trans('Product'),
        ]);
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
        CRUD::setValidation(CategoryRequest::class);

        CRUD::field('name')
            ->label(trans('Name'));
        CRUD::field('description')
            ->label(trans('Description'))
            ->type('tinymce');
        CRUD::field('image')
            ->label(trans('Image'))
            ->type('image')
            ->withFiles(['disk' => 'category']);
        CRUD::field('alt')
            ->label(trans('Alt text'));
        CRUD::addField([
            'name' => 'search_url',
            'label' => trans('Search URL'),
            'type' => 'slug',
            'target' => 'name',
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
                    'disk' => 'category',
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
        ]);
    }
}
