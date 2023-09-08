<?php

namespace app\modules\budget\events;

use yii\base\Event;

/**
 * Gets data from event than handle it with logging
 *
 * Class DeleteBudgetItemHandler
 * @package app\modules\budget\events
 */
class DeleteBudgetItemHandler extends BudgetItemHandler
{
    /**
     * logs handled data
     *
     * @param DeleteBudgetItemEvent $event
     */
    public function log(Event $event): void
    {
        parent::log($event);
    }

    /**
     * Gets init data from event
     *
     * @param DeleteBudgetItemEvent $event
     * @return array
     */
    public function getInitDataForLog(Event $event): array
    {
        return (array)$event->getBudgetItem();
    }

    /**
     * Gets data to logging
     *
     * @param array $initData
     * @return string
     */
    public function getMessageForLog(array $initData): string
    {
        $message = 'The properties are deleted: ' . PHP_EOL;
        $message .= $this->getKeyValueLinesForLog($initData);

        return $message;
    }
}
