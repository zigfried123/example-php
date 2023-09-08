<?php

namespace app\modules\budget\events;

use app\modules\budget\models\BudgetItem;
use yii\base\Event;

class UpdateBudgetItemEvent extends Event
{
    /**
     * @var BudgetItem $budgetItem
     */
    private $budgetItem;

    /**
     * @var BudgetItem $budgetItemOld
     */
    private $budgetItemOld;

    public function __construct(BudgetItem $budgetItem, BudgetItem $budgetItemOld, array $config = [])
    {
        $this->budgetItem = $budgetItem;
        $this->budgetItemOld = $budgetItemOld;
        parent::__construct($config);
    }

    /**
     * @return BudgetItem
     */
    public function getBudgetItem(): BudgetItem
    {
        return $this->budgetItem;
    }

    /**
     * @return BudgetItem
     */
    public function getBudgetItemOld(): BudgetItem
    {
        return $this->budgetItemOld;
    }
}
