<?php

namespace app\modules\budget\repositories;

use app\modules\budget\models\Task;
use app\modules\budget\repositories\exceptions\EntityDeleteException;
use app\modules\budget\repositories\exceptions\EntityNotFoundException;
use app\modules\budget\repositories\exceptions\EntitySaveErrorException;
use Throwable;
use yii\db\StaleObjectException;

class TaskRepository
{
    /**
     * @param Task $model
     * @throws EntitySaveErrorException
     */
    public function save(Task $model)
    {
        if (!$model->save()) {
            throw new EntitySaveErrorException(
                'Task model dont saved' . json_encode(
                    $model->errors,
                    JSON_UNESCAPED_UNICODE
                )
            );
        }
    }

    /**
     * @param Task $model
     * @throws EntityDeleteException
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function delete(Task $model)
    {
        if (!$model->delete()) {
            throw new EntityDeleteException('Task model dont deleted');
        }
    }

    /**
     * @param int $id
     * @return Task
     * @throws EntityNotFoundException
     */
    public function getById(int $id): Task
    {
        /** @var Task $task */
        $task = Task::find()
                ->where(['id' => $id])
                ->one();

        if ($task === null) {
            throw new EntityNotFoundException('Task with id ' . $id . ' not found');
        }

        return $task;
    }

    /**
     * @param int $id
     * @return Task
     * @throws EntityNotFoundException
     */
    public function getByTaskId(int $id): Task
    {
        /** @var Task $task */
        $task = Task::find()
            ->where(['task_id' => $id])
            ->one();

        if ($task === null) {
            throw new EntityNotFoundException('Task with task_id ' . $id . ' not found');
        }

        return $task;
    }
}
