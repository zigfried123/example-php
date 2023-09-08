<?php

namespace app\modules\budget\jobs\task;

use app\modules\budget\repositories\TaskRepository;
use app\modules\budget\services\TaskService;
use Throwable;
use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\RetryableJobInterface;

class DeleteTaskJob extends BaseObject implements JobInterface, RetryableJobInterface
{
    const TIME_REPEAT = 600;

    /** @var int */
    public $id;

    /** @var TaskService */
    private $taskService;

    /** @var TaskRepository */
    private $tasks;

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->taskService = Yii::$container->get(TaskService::class);
        $this->tasks = Yii::$container->get(TaskRepository::class);
    }

    /**
     * @inheritDoc
     */
    public function execute($queue)
    {
        $task = $this->tasks->getByTaskId($this->id);
        $this->taskService->delete($task);
    }

    /**
     * @inheritDoc
     */
    public function getTtr()
    {
        return self::TIME_REPEAT;
    }

    /**
     * @inheritDoc
     */
    public function canRetry($attempt, $error)
    {
        return ($error instanceof Throwable);
    }
}
