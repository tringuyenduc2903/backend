<?php

namespace App\Enums;

use Rexlabs\Enum\Enum;

/**
 * The EmployeePermissionEnum enum.
 *
 * @method static self DASHBOARD()
 * @method static self EMPLOYEE_CRUD()
 * @method static self ROLE_CRUD()
 */
class EmployeePermissionEnum extends Enum
{
    const DASHBOARD = 'dashboard';

    const EMPLOYEE_CRUD = 'employee_crud';

    const ROLE_CRUD = 'role_crud';
}
