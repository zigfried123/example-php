<?php

namespace app\modules\budget\providers\contracts;

use app\modules\budget\resources\ResourceBudgetDepartment;

interface BudgetDepartmentProviderInterface
{
    /**
     * @return ResourceBudgetDepartment[]
     */
    public function getAll(): array;
}
