<?php

namespace app\modules\budget\providers\contracts;

use app\modules\budget\resources\ResourceBudgetTask;

interface BudgetTaskProviderInterface
{
    public function get(int $id): ResourceBudgetTask;
}
