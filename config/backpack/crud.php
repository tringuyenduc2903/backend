<?php

/**
 * Backpack\CRUD preferences.
 */

use Backpack\CRUD\app\Library\Uploaders\MultipleFiles;
use Backpack\CRUD\app\Library\Uploaders\SingleBase64Image;
use Backpack\CRUD\app\Library\Uploaders\SingleFile;
use Backpack\CRUD\app\Library\Uploaders\Support\FileNameGenerator;

return [

    /*
    |-------------------
    | TRANSLATABLE CRUD'S
    |-------------------
    */

    'show_translatable_field_icon' => true,
    'translatable_field_icon_position' => 'right', // left or right

    'locales' => [
        'vi' => 'Tiếng Việt',
    ],

    'view_namespaces' => [
        'buttons' => [
            'crud::buttons', // falls back to 'resources/views/vendor/backpack/crud/buttons'
        ],
        'columns' => [
            'crud::columns', // falls back to 'resources/views/vendor/backpack/crud/columns'
        ],
        'fields' => [
            'crud::fields', // falls back to 'resources/views/vendor/backpack/crud/fields'
        ],
        'filters' => [
            'crud::filters', // falls back to 'resources/views/vendor/backpack/crud/filters'
        ],
    ],
    // the uploaders for the `withFiles` macro
    'uploaders' => [
        'withFiles' => [
            'image' => SingleBase64Image::class,
            'upload' => SingleFile::class,
            'upload_multiple' => MultipleFiles::class,
        ],
    ],

    'file_name_generator' => FileNameGenerator::class,

];
