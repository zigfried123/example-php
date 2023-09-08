<?php

namespace app\modules\budget\events;

use app\modules\budget\models\Task;
use yii\base\Event;

class UpdateTaskEvent extends Event
{
    /** @var Task */
    private $new;

    /** @var Task */
    private $old;

    public function __construct(Task $new, Task $old, array $config = [])
    {
        $this->new = $new;
        $this->old = $old;

        parent::__construct($config);
    }

    public function getNew(): Task
    {
        return $this->new;
    }

    public function getOld(): Task
    {
        return $this->old;
    }
}
