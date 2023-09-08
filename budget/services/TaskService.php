<?php

namespace app\modules\budget\services;

use app\modules\budget\events\DeleteTaskEvent;
use app\modules\budget\events\NewTaskEvent;
use app\modules\budget\events\UpdateTaskEvent;
use app\modules\budget\models\Task;
use app\modules\budget\repositories\exceptions\EntityDeleteException;
use app\modules\budget\repositories\exceptions\EntityNotFoundException;
use app\modules\budget\repositories\exceptions\EntitySaveErrorException;
use app\modules\budget\repositories\TaskRepository;
use app\modules\budget\services\exceptions\ServiceException;
use Throwable;
use yii\base\Component;
use yii\db\StaleObjectException;

class TaskService extends Component
{
    /** @var TaskRepository $tasks */
    private $tasks;

    public function __construct(TaskRepository $tasks, $config = [])
    {
        parent::__construct($config);
        $this->tasks = $tasks;
    }

    /**
     * @param Task $model
     * @throws ServiceException
     */
    public function add(Task $model)
    {
        try {
            $this->tasks->save($model);
            $this->trigger(Task::EVENT_NEW_TASK, new NewTaskEvent($model));
        } catch (EntitySaveErrorException $e) {
            throw new ServiceException($e->getMessage());
        }
    }

    /**
     * @param Task $model
     * @throws ServiceException
     * @throws EntityNotFoundException
     */
    public function update(Task $model)
    {
        try {
            $old = $this->tasks->getById($model->id);
            $this->tasks->save($model);
            $this->trigger(Task::EVENT_CHANGE_TASK, new UpdateTaskEvent($model, $old));
        } catch (EntitySaveErrorException $e) {
            throw new ServiceException($e->getMessage());
        }
    }

    /**
     * @param Task $model
     * @return bool
     * @throws ServiceException
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function delete(Task $model): bool
    {
        try {
            $this->tasks->delete($model);
            $this->trigger(Task::EVENT_DELETE_TASK, new DeleteTaskEvent($model));
        } catch (EntityDeleteException $e) {
            throw new ServiceException($e->getMessage());
        }
    }
}
