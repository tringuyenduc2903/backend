<?php

namespace App\Enums;

use Rexlabs\Enum\Enum;

/**
 * The EmployeePermissionEnum enum.
 *
 * @method static self DASHBOARD()
 * @method static self EMPLOYEE_CRUD()
 */
class EmployeePermissionEnum extends Enum
{
    const DASHBOARD = 'dashboard';

    const EMPLOYEE_CRUD = 'employee_crud';
}
