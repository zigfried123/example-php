<?php

namespace app\modules\budget\providers\contracts;

use app\modules\budget\resources\ResourceBudgetItem;

interface BudgetProviderInterface
{
    /**
     * @return ResourceBudgetItem[]
     */
    public function getAll();
}
