<?php

namespace app\modules\budget\events;

use app\components\helpers\ArrayHelperCustom;
use Yii;
use yii\base\Event;

/**
 * Gets data from event than handle it with log
 *
 * Class BudgetItemHandler
 * @package app\modules\budget\events
 */
abstract class BudgetItemHandler
{
    abstract public function getInitDataForLog(Event $event);

    abstract public function getMessageForLog(array $initData);

    /**
     * logs handled data
     *
     * @param $event
     */
    protected function log(Event $event): void
    {
        $initData = $this->getInitDataForLog($event);

        $message = $this->getMessageForLog($initData);

        Yii::info($message);
    }

    /**
     * Gets keyValue lines to logging
     *
     * @param array $initData
     * @return string
     */
    protected function getKeyValueLinesForLog(array $initData): string
    {
        return ArrayHelperCustom::getKeyValueMap($initData, function ($keyValueMap) {
            return ArrayHelperCustom::getKeyValueLines($keyValueMap);
        });
    }
}
