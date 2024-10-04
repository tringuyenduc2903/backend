<x-backpack::menu-item :title="trans('backpack::base.dashboard')" :link="route('backpack.dashboard')"
                       icon="la la-home nav-icon"/>
@php
    use App\Enums\EmployeePermission;
    use App\Models\Employee;

    $menu_items = [
        [
            'title' => trans('Sales'),
            'icon' => 'la la-dollar',
            'columns' => [
                [
                    'items' => [
                        [
                            'permission' => EmployeePermission::ORDER_CRUD,
                            'title' => trans('Orders'),
                            'link' => route('orders.index'),
                        ],
                        [
                            'permission' => EmployeePermission::SHIPMENT_CRUD,
                            'title' => trans('Shipments'),
                            'link' => route('shipments.index'),
                        ],
                    ],
                ],
            ],
        ],
        [
            'title' => trans('Sales (Motorcycle)'),
            'icon' => 'la la-dollar',
            'columns' => [
                [
                    'items' => [
                        [
                            'permission' => EmployeePermission::ORDER_CRUD,
                            'title' => trans('Order'),
                            'link' => route('order-motorcycles.index'),
                        ],
                    ],
                ],
            ],
        ],
        [
            'title' => trans('Catalog'),
            'icon' => 'la la-puzzle-piece',
            'columns' => [
                [
                    'items' => [
                        [
                            'permission' => EmployeePermission::PRODUCT_CRUD,
                            'title' => trans('Products'),
                            'link' => route('products.index'),
                        ],
                        [
                            'permission' => EmployeePermission::CATEGORY_CRUD,
                            'title' => trans('Categories'),
                            'link' => route('categories.index'),
                        ],
                        [
                            'permission' => EmployeePermission::MOTOR_CYCLE_CRUD,
                            'title' => trans('Motor cycles'),
                            'link' => route('motor-cycles.index'),
                        ],
                        [
                            'permission' => EmployeePermission::REVIEW_CRUD,
                            'title' => trans('Reviews'),
                            'link' => route('reviews.index'),
                        ],
                    ],
                ],
            ],
        ],
        [
            'title' => trans('Customers'),
            'icon' => 'la la-user',
            'columns' => [
                [
                    'items' => [
                        [
                            'permission' => EmployeePermission::CUSTOMER_CRUD,
                            'title' => trans('All customers'),
                            'link' => route('customers.index'),
                        ],
                    ],
                ],
            ],
        ],
        [
            'title' => trans('Branches'),
            'icon' => 'la la-shopping-cart',
            'columns' => [
                [
                    'items' => [
                        [
                            'permission' => EmployeePermission::BRANCH_CRUD,
                            'title' => trans('All branches'),
                            'link' => route('branches.index'),
                        ],
                    ],
                ],
            ],
        ],
        [
            'title' => trans('System'),
            'icon' => 'la la-gear',
            'columns' => [
                [
                    'title' => trans('Store'),
                    'items' => [
                        [
                            'permission' => EmployeePermission::SETTING_CRUD,
                            'title' => trans('All settings'),
                            'link' => route('settings.index'),
                        ],
                    ],
                ],
                [
                    'title' => trans('Permissions'),
                    'items' => [
                        [
                            'permission' => EmployeePermission::EMPLOYEE_CRUD,
                            'title' => trans('All employees'),
                            'link' => route('employees.index'),
                        ],
                        [
                            'permission' => EmployeePermission::ROLE_CRUD,
                            'title' => trans('Employee roles'),
                            'link' => route('roles.index'),
                        ],
                    ],
                ],
            ],
        ],
    ];
@endphp
@foreach ($menu_items as $item)
    @php
        $permissions = [];

        foreach ($item['columns'] as $column) {
            foreach ($column['items'] as $item_column) {
                $permissions[] = $item_column['permission'];
            }
        }

        /** @var Employee $employee */
        $employee = backpack_user();
    @endphp
    @if ($employee->hasAnyPermission($permissions, config('backpack.base.guard')))
        <x-backpack::menu-dropdown :title="$item['title']" :icon="$item['icon']">
            @foreach ($item['columns'] as $column)
                <x-theme-tabler::menu-dropdown-column>
                    @php($permissions = array_column($column['items'], 'permission'))
                    @isset($column['title'])
                        @if ($employee->hasAnyPermission($permissions, config('backpack.base.guard')))
                            <x-backpack::menu-dropdown-header :title="$column['title']"/>
                        @endif
                    @endisset
                    @foreach ($column['items'] as $item)
                        @if ($employee->hasPermissionTo($item['permission'], config('backpack.base.guard')))
                            <x-backpack::menu-dropdown-item :title="$item['title']" :link="$item['link']"/>
                        @endif
                    @endforeach
                </x-theme-tabler::menu-dropdown-column>
            @endforeach
        </x-backpack::menu-dropdown>
    @endif
@endforeach
