<?php

namespace app\modules\budget\events;

use yii\base\Event;

/**
 * Gets data from event than handle it with logging
 *
 * Class NewBudgetItemHandler
 * @package app\modules\budget\events
 */
class NewBudgetItemHandler extends BudgetItemHandler
{
    /**
     * logs handled data
     *
     * @param NewBudgetItemEvent $event
     */
    public function log(Event $event): void
    {
        parent::log($event);
    }

    /**
     * Gets init data from event
     *
     * @param NewBudgetItemEvent $event
     * @return array
     */
    public function getInitDataForLog(Event $event): array
    {
        return (array)$event->getBudgetItem();
    }

    /**
     * Get data to logging
     *
     * @param array $initData
     * @return string
     */
    public function getMessageForLog(array $initData): string
    {
        $message = 'The properties are created: ' . PHP_EOL;
        $message .= $this->getKeyValueLinesForLog($initData);

        return $message;
    }
}
