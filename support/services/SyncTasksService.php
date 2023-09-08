<?php

namespace app\modules\support\services;

use app\components\helpers\DateHelper;
use app\modules\support\ar\SupportTaskStatus;
use app\modules\support\models\status\Status;
use DateTime;
use DateTimeImmutable;
use Exception;
use Throwable;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class SyncTasksService
 * @package app\modules\support\services
 */
class SyncTasksService extends BaseService
{
    /**
     * @var string
     */
    private $table = 'support_task_status';

    /**
     *
     * @param Status[] $statuses
     * @throws \yii\db\Exception
     */
    public function save($statuses)
    {
        $this->transactionStart();

        try {
            foreach ($statuses as $status) {
                $sql = 'SELECT * FROM ' . $this->table . ' WHERE action_id = ' . $status->getActionId() .
                    ' AND status = ' . $status->getType()->getCode();

                $row = Yii::$app->dbPlanfixSync->createCommand($sql)->queryOne();

                if (!$row) {
                    Yii::$app->dbPlanfixSync->createCommand()->insert($this->table, [
                        'action_id' => $status->getActionId(),
                        'task_id' => $status->getTaskId(),
                        'task_num' => $status->getTaskNum(),
                        'emergency' => $status->isEmergency(),
                        'department' => $status->getDepartment(),
                        'support_service' => $status->getSupportService(),
                        'service_class' => $status->getServiceClass(),
                        'tags' => $status->getTags(),
                        'status' => $status->getType()->getCode(),
                        'status_name' => $status->getType()->getTitle(),
                        'name' => $status->getTaskName(),
                        'created' => $status->getDatetime()->format('Y-m-d H:i:s'),
                    ])->execute();
                }
            }

            $this->transactionCommit();
        } catch (Throwable $exception) {
            $this->transactionRollback();
            throw $exception;
        }
    }

    /**
     * Получает список задач для определенного периода с выбором наиболее длительного статуса по дням и в зависимости от включения или исключения нерабочих дней
     * Если task_id не включен в период, но присутствует до, тогда по нему берется статус за последний перед периодом день
     *
     * @param string $startDate
     * @param string $endDate
     * @param bool $daysOff
     * @return array
     * @throws Exception
     */
    public function getTasksForPeriod(string $startDate, string $endDate, bool $daysOff): array
    {
        try {
            $tasksInPeriod = $this->getTasksInPeriod($startDate, $endDate);

            $beforePeriodTasks = $this->getTasksBeforePeriod($startDate);

            $tasksByCreated = $this->indexTasksByCreated(array_merge($tasksInPeriod, $beforePeriodTasks));

            if ($daysOff) {
                $this->filterTasksOutDayOff($tasksByCreated);
            }

            $tasksByCreated = $this->getTasksWithLongerStatusByDays($tasksByCreated);

            $tasksByCreated = $this->fetchAllTasksTillEndPeriodByPeriodDates($startDate, $endDate, $tasksByCreated, $daysOff);

            $tasksByCreated = array_reverse($tasksByCreated);

            return $tasksByCreated;

        } catch (Exception $e) {

            Yii::error($e->getMessage());
            return [];
        }
    }

    /**
     * Получает все задачи по каждому дню в течение периода
     *
     * @param $startDate
     * @param $endDate
     * @param $tasksByCreated
     * @return array
     * @throws Exception
     */
    private function fetchAllTasksTillEndPeriodByPeriodDates($startDate, $endDate, $tasksByCreated, $daysOff): array
    {
        $this->resetTaskKeys($tasksByCreated);

        // Все таски в и до периода
        $allTasksByTaskId = $this->indexTasksByTaskId($tasksByCreated);

        // Все даты в течение периода
        $allDatesInPeriod = $this->getDatesForPeriod($startDate, $endDate, $daysOff);

        $tasksByAllDates = [];

        foreach ($allDatesInPeriod as $date) {

            foreach ($allTasksByTaskId as $taskId => $byTaskId) {

                $reverse = array_reverse($byTaskId);

                foreach ($reverse as $created => $task) {

                    if ($created <= $date) {
                        $tasksByAllDates[$date][$taskId] = current($task);
                        break;
                    }
                }
            }
        }

        return $tasksByAllDates;
    }

    /**
     * Получает массив с датами в формате Y-m-d в обратном порядке
     *
     * @param $startDate
     * @param $endDate
     * @param $daysOff
     * @return array
     */
    private function getDatesForPeriod($startDate, $endDate, $daysOff): array
    {
        $allDates = range(strtotime($endDate), strtotime($startDate), 86400);

        if($daysOff) {
            $allDates = array_filter($allDates, function ($timestamp) {

                $dateTime = new DateTime('@' . $timestamp);

                return !$this->isDayOff($dateTime);
            });
        }

        foreach ($allDates as &$date) {
            $date = date('Y-m-d', $date);
        }

        return $allDates;
    }

    /**
     * Получает задачи c наиболее длительными по дням статусами
     *
     * @param array $tasksByCreated
     * @return array
     */
    private function getTasksWithLongerStatusByDays(array $tasksByCreated): array
    {
        $this->setDurationByStatus($tasksByCreated);

        $this->filterTaskByMaxStatusDuration($tasksByCreated);

        return $tasksByCreated;
    }

    /**
     * Получает все задачи за период
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     * @throws Exception
     */
    private function getTasksInPeriod(string $startDate, string $endDate): array
    {
        $startDate = (new DateTime($startDate))->format('Y-m-d');
        $endDate = (new DateTime($endDate))->format('Y-m-d');

        $sql = "SELECT * FROM {$this->table} WHERE `created` BETWEEN CAST(:startDate AS DATE) AND CAST(:endDate AS DATE) ORDER BY UNIX_TIMESTAMP(`created`) ASC";

        $tasks = Yii::$app->dbPlanfixSync->createCommand($sql)
            ->bindValues([
                ':startDate' => $startDate,
                ':endDate' => $endDate,
            ])
            ->queryAll();

        return $tasks;
    }

    /**
     * Получает задачи до периода, если task_id в периоде отсутствует
     *
     * @param string $startDate
     * @return array
     * @throws Exception
     */
    private function getTasksBeforePeriod(string $startDate): array
    {
        $startDate = (new DateTime($startDate))->format('Y-m-d');

        $sql = "SELECT * FROM {$this->table} WHERE `created` < CAST(:startDate as DATE) ORDER BY task_id, created ASC";

        $tasks = Yii::$app->dbPlanfixSync->createCommand($sql)
            ->bindValues([':startDate' => $startDate])
            ->queryAll();

        $tasks = $this->getTasksByLastDayBeforePeriod($tasks);

        $this->resetTaskKeys($tasks);

        return $tasks;
    }

    /**
     * Получает последний день статуса до периода, если task_id в нем отсутствует
     *
     * @param array $tasks
     * @return array
     * @throws Exception
     */
    private function getTasksByLastDayBeforePeriod(array $tasks): array
    {
        $tasks = $this->indexTasksByTaskId($tasks);

        foreach ($tasks as $taskId => &$tasksById) {
            $lastDayTask = end($tasksById);
            $tasksById = $lastDayTask;
        }

        return $tasks;
    }

    /**
     * Сбрасывает ключи и возвращает двумерный массив
     * Применять только до маппинга по всем датам!
     *
     * @param array $tasks
     */

    public function resetTaskKeys(array &$tasks)
    {
        if (!empty($tasks)) {
            $tasks = call_user_func_array('array_merge_recursive', $tasks);
        }
    }


    /**
     * @param array $tasks
     */
    private function filterTaskByMaxStatusDuration(array &$tasks)
    {
        foreach ($tasks as $created => &$tasksByCreated) {
            foreach ($tasksByCreated as $taskId => &$tasksById) {

                $tasksById = array_reduce($tasksById, function ($prev, $cur) {

                    if (empty($prev) || $cur['duration'] > $prev['duration']) {
                        return $cur;
                    }

                    return $prev;
                });
            }
        }
    }

    /**
     * @param array $tasks
     */
    private function setDurationByStatus(array &$tasks)
    {
        foreach ($tasks as $created => &$tasksByCreated) {
            foreach ($tasksByCreated as $taskId => &$tasksById) {

                $durations = [];

                foreach ($tasksById as $key => &$task) {

                    if(!isset($durations[$task['status_name']])){
                        $durations[$task['status_name']] = 0;
                    }

                    if (isset($tasksById[$key + 1])) {
                        $durations[$task['status_name']] += strtotime($tasksById[$key + 1]['created']) - strtotime($task['created']);
                    } else {

                        $time = strtotime($task['created']);
                        $month = date('m', $time);
                        $year = date('Y', $time);
                        $day = date('d', $time);

                        $durations[$task['status_name']] += mktime(0, 0, 0, $month, $day + 1, $year) - $time;
                    }

                    $task['duration'] = $durations[$task['status_name']];

                    $task['duration_min'] = round($task['duration'] / 60);
                }
            }
        }
    }

    /**
     * Фильтрует список задач по нерабочим дням
     *
     * @param array $tasks
     * @throws Exception
     */
    private function filterTasksOutDayOff(array &$tasks)
    {
        foreach ($tasks as $Ymd => $task) {
            $date = new DateTime($Ymd);
            if ($this->isDayOff($date)) {
                unset($tasks[$Ymd]);
            }
        }
    }

    /**
     * Проверяет является ли день выходным или праздником
     *
     * @param DateTime $dateTime
     * @return bool
     * @throws \app\exceptions\DateHelperException
     */
    private function isDayOff(DateTime $dateTime): bool
    {
        $holidays = DateHelper::getDaysOff();

        $md = $dateTime->format('m.d');

        return in_array($md, $holidays) || $dateTime->format('N') >= 6;
    }

    /**
     * Индексирует массив задач по колонке created в формате Y-m-d
     *
     * @param array $tasks
     * @return array
     * @throws Exception
     */
    public function indexTasksByCreated(array $tasks): array
    {
        $tasksByCreated = [];
        foreach ($tasks as $task) {
            $created = (new DateTime($task['created']))->format('Y-m-d');
            $taskId = $task['task_id'];
            $tasksByCreated[$created][$taskId][] = $task;
        }

        return $tasksByCreated;
    }

    /**
     * Индексирует массив задач по колонке task_id в формате Y-m-d
     *
     * @param array $tasks
     * @return array
     * @throws Exception
     */
    public function indexTasksByTaskId(array $tasks): array
    {
        $tasksByCreated = [];

        foreach ($tasks as $task) {

            $created = (new DateTime($task['created']))->format('Y-m-d');
            $taskId = $task['task_id'];
            $tasksByCreated[$taskId][$created][] = $task;
        }

        return $tasksByCreated;
    }

    /**
     * Очистка таблицы перед получением полных данных
     */
    public function truncate()
    {
        Yii::$app->dbPlanfixSync->createCommand()->truncateTable($this->table);
    }

    /**
     * @return false|string|null
     * @throws \yii\db\Exception
     */
    public function getLastDateCreated()
    {
        $sql = 'SELECT max(created) FROM support_task_status';

        return Yii::$app->dbPlanfixSync->createCommand($sql)->queryScalar();
    }

    /**
     * @param DateTimeImmutable $from
     * @param DateTimeImmutable $to
     * @return SupportTaskStatus[]
     */
    public function cfdAmountTasks(DateTimeImmutable $from, DateTimeImmutable $to): array
    {
        return SupportTaskStatus::find()
            ->where([
                'and',
                ['>', 'created', $from->format('Y-m-d H:i:s')],
                ['<', 'created', $to->format('Y-m-d H:i:s')]])
            ->all();
    }

    /**
     * Переиндексирует массив по task_id
     *
     * @param $tasks
     * @return array
     */
    public function toByTaskId($tasks): array
    {
        $tasksByTaskId = [];

        foreach ($tasks as $created => $byCreated) {
            foreach ($byCreated as $taskId => $byTaskId) {
                $tasksByTaskId[$taskId][$created] = $byTaskId;
            }
        }

        return $tasksByTaskId;
    }

    /**
     * Переиндексирует массив по created
     *
     * @param $tasks
     * @return array
     */
    public function toByCreated($tasks): array
    {
        $tasksByCreated = [];

        foreach ($tasks as $taskId => $byTaskId) {
            foreach ($byTaskId as $created => $byCreated) {
                $tasksByCreated[$created][$taskId] = $byCreated;
            }
        }

        return $tasksByCreated;
    }
}
