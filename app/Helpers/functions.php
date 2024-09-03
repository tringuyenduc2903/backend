<?php

use App\Models\Employee;

if (! function_exists('revoke_token')) {
    function revoke_token($token_name): mixed
    {
        return request()->user()->tokens()->whereName($token_name)->delete();
    }
}

if (! function_exists('regenerate_token')) {
    function regenerate_token($token_name): string
    {
        revoke_token($token_name);

        return request()->user()->createToken($token_name)->plainTextToken;
    }
}

if (! function_exists('deny_access')) {
    function deny_access(string $permission): void
    {
        /** @var Employee $employee */
        $employee = backpack_user();

        if ($employee->hasPermissionTo(
            $permission,
            config('backpack.base.guard')
        )) {
            return;
        }

        CRUD::denyAllAccess();
    }
}
