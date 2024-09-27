<?php

namespace App\Enums;

use Rexlabs\Enum\Enum;

/**
 * The EmployeePermission enum.
 *
 * @method static self DASHBOARD()
 * @method static self API_DOCS()
 * @method static self EMPLOYEE_CRUD()
 * @method static self ROLE_CRUD()
 * @method static self BRANCH_CRUD()
 * @method static self CUSTOMER_CRUD()
 * @method static self SETTING_CRUD()
 * @method static self PRODUCT_CRUD()
 * @method static self CATEGORY_CRUD()
 * @method static self MOTOR_CYCLE_CRUD()
 * @method static self REVIEW_CRUD()
 * @method static self ORDER_CRUD()
 */
class EmployeePermission extends Enum
{
    const DASHBOARD = 'dashboard';

    const API_DOCS = 'api_docs';

    const EMPLOYEE_CRUD = 'employee_crud';

    const ROLE_CRUD = 'role_crud';

    const BRANCH_CRUD = 'branch_crud';

    const CUSTOMER_CRUD = 'customer_crud';

    const SETTING_CRUD = 'setting_crud';

    const PRODUCT_CRUD = 'product_crud';

    const CATEGORY_CRUD = 'category_crud';

    const MOTOR_CYCLE_CRUD = 'motor_cycle_crud';

    const REVIEW_CRUD = 'review_crud';

    const ORDER_CRUD = 'order_crud';
}
