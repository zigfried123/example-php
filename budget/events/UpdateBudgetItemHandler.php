<?php

namespace app\modules\budget\events;

use yii\base\Event;

/**
 * Gets data from event than handle it with logging
 *
 * Class UpdateBudgetItemHandler
 * @package app\modules\budget\events
 */
class UpdateBudgetItemHandler extends BudgetItemHandler
{
    /**
     * logs handled data
     *
     * @param UpdateBudgetItemEvent $event
     * @param $event
     */
    public function log(Event $event): void
    {
        parent::log($event);
    }

    /**
     * Gets init data from event
     *
     * @param UpdateBudgetItemEvent $event
     * @return array
     */
    public function getInitDataForLog(Event $event): array
    {
        $budgetItem = (array)$event->getBudgetItem();
        $budgetItemOld = (array)$event->getBudgetItemOld();

        return array_diff($budgetItem, $budgetItemOld);
    }

    /**
     * Gets data to logging
     *
     * @param array $initData
     * @return string
     */
    public function getMessageForLog(array $initData): string
    {
        $message = 'The properties are updated: ' . PHP_EOL;
        $message .= $this->getKeyValueLinesForLog($initData);

        return $message;
    }

}
