<x-backpack::menu-item
    :title="trans('backpack::base.dashboard')"
    :link="backpack_url('dashboard')"
    icon="la la-home nav-icon"
/>
@php
    use App\Enums\EmployeePermissionEnum;

    $menu_items = [
        [
            'title' => trans('System'),
            'icon' => 'la la-gear',
            'columns' => [
                [
                    'title' => trans('Permissions'),
                    'items' => [
                        [
                            'permission' => EmployeePermissionEnum::EMPLOYEE_CRUD,
                            'title' => trans('All employees'),
                            'link' => backpack_url('employees'),
                        ],
                        [
                            'permission' => EmployeePermissionEnum::ROLE_CRUD,
                            'title' => trans('Employee roles'),
                            'link' => backpack_url('roles'),
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

        /** @var \App\Models\Employee $employee */
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
