<?php

namespace app\modules\budget\events;

use app\modules\budget\models\Task;
use yii\base\Event;

class NewTaskEvent extends Event
{
    /** @var Task */
    private $task;

    public function __construct(Task $task, $config = [])
    {
        $this->task = $task;
        parent::__construct($config);
    }

    public function getTask(): Task
    {
        return $this->task;
    }
}
