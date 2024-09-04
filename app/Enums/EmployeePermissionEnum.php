<?php

namespace App\Enums;

use Rexlabs\Enum\Enum;

/**
 * The EmployeePermissionEnum enum.
 *
 * @method static self DASHBOARD()
 * @method static self EMPLOYEE_CRUD()
 * @method static self ROLE_CRUD()
 * @method static self BRANCH_CRUD()
 * @method static self CUSTOMER_CRUD()
 * @method static self SETTING_CRUD()
 */
class EmployeePermissionEnum extends Enum
{
    const DASHBOARD = 'dashboard';

    const EMPLOYEE_CRUD = 'employee_crud';

    const ROLE_CRUD = 'role_crud';

    const BRANCH_CRUD = 'branch_crud';

    const CUSTOMER_CRUD = 'customer_crud';

    const SETTING_CRUD = 'setting_crud';
}
