<?php

namespace app\modules\budget\events;

use app\modules\budget\models\BudgetItem;
use yii\base\Event;

class DeleteBudgetItemEvent extends Event
{
    /**
     * @var BudgetItem $budgetItem
     */
    private $budgetItem;

    public function __construct(BudgetItem $budgetItem, $config = [])
    {
        $this->budgetItem = $budgetItem;
        parent::__construct($config);
    }

    /**
     * @return BudgetItem
     */
    public function getBudgetItem(): BudgetItem
    {
        return $this->budgetItem;
    }
}
