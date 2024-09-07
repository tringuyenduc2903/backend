<?php

namespace Database\Seeders;

use App\Enums\EmployeePermissionEnum;
use App\Enums\EmployeeRoleEnum;
use App\Models\Branch;
use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->permissions();
        $this->roles();
        $this->admin();
    }

    protected function permissions(): void
    {
        foreach (EmployeePermissionEnum::keys() as $permission) {
            Permission::createOrFirst([
                'name' => $permission,
            ], [
                'guard_name' => config('backpack.base.guard'),
            ]);
        }
    }

    protected function roles(): void
    {
        foreach (EmployeeRoleEnum::keys() as $role) {
            $role_db = Role::createOrFirst([
                'name' => $role,
            ], [
                'guard_name' => config('backpack.base.guard'),
            ]);

            if ($role === EmployeeRoleEnum::ADMIN) {
                $role_db->givePermissionTo(Permission::all());
            }
        }
    }

    protected function admin(): void
    {
        $admin = Employee::createOrFirst([
            'email' => 'admin@example.com',
        ], [
            'name' => 'Admin',
            'password' => 'admin',
            'remember_token' => Str::random(10),
        ]);

        $admin->branch()
            ->associate(Branch::inRandomOrder()->first())
            ->save();

        $admin->assignRole(
            Role::whereName(EmployeeRoleEnum::ADMIN)->firstOrFail()
        );
    }
}
