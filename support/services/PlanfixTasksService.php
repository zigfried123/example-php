<?php

namespace app\modules\support\services;

use app\common\exceptions\PlanfixErrorException;
use app\modules\support\dto\TaskDTO;
use app\modules\support\models\action\Action;
use app\modules\support\models\status\Status;
use app\modules\support\models\status\Type;
use app\modules\support\repositories\TasksStatusesRepository;
use app\modules\tickets\helpers\ApiPlanfix;
use DateTime;
use Exception;
use RuntimeException;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

class PlanfixTasksService extends BaseService
{
    /** @var TasksStatusesRepository */
    private $planfixTasks;

    /** @var ApiPlanfix */
    private $apiPlanfix;


    /**
     * PlanfixTasksService constructor.
     * @param TasksStatusesRepository $planfixTaskRepository
     */
    public function __construct(
        TasksStatusesRepository $planfixTaskRepository,
        ApiPlanfix $apiPlanfix
    )
    {
        $this->planfixTasks = $planfixTaskRepository;
        $this->apiPlanfix = $apiPlanfix;

        parent::__construct();
    }

    /**
     * @param DateTime $fromDate
     * @param int $sleepBetweenRequest
     * @return TaskDTO[]
     * @throws Exception
     */
    public function getTasks(DateTime $fromDate, int $sleepBetweenRequest = 0): array
    {
        $pageCurrent = 1;
        $pageSize = 100;

        $response = $this->planfixTasks->getList($fromDate, $pageCurrent, $pageSize);

        $totalCount = $response['tasks']['@attributes']['totalCount'];
        $tasks = [];

        if (!empty($response['tasks']['task'])) {
            $tasks = $response['tasks']['task'];
        }

        $countPages = ceil($totalCount / $pageSize) - 1;

        for ($i = 0; $i < $countPages; $i++) {

            Console::updateProgress($i + 1, $countPages, 'Получение задач');

            if ($sleepBetweenRequest) {
                sleep($sleepBetweenRequest);
            }

            $pageCurrent = $i + 2;
            $response = $this->planfixTasks->getList($fromDate, $pageCurrent, $pageSize);
            $tasks = ArrayHelper::merge($tasks, $response['tasks']['task']);
        }

        Console::endProgress();

        $result = [];

        foreach ($tasks as $task) {

            $taskName = $task['title'];

            if (isset($task['customData']['customValue'])) {

                $department = '';
                $serviceClass = '';
                $supportService = '';
                $tags = '';


                foreach ($task['customData']['customValue'] as $val) {
                    if (!isset($val['field']['name'])) {
                        continue;
                    }

                    switch ($val['field']['name']) {
                        case 'Теги':
                            $tags = $val['text'];
                            break;
                        case  'Класс обслуживания ТП';
                            $serviceClass = $val['text'];
                            break;
                        case  'Сервис техподдержки';
                            $supportService = $val['text'];
                            break;
                        case  'Отдел постановщик задачи';
                            $department = $val['text'];
                            break;
                    }
                }
            }

            $department = is_array($department) ? implode('.', $department) : $department;

            $serviceClass = is_array($serviceClass) ? implode('.', $serviceClass) : $serviceClass;

            $supportService = is_array($supportService) ? implode('.', $supportService) : $supportService;

            $tags = is_array($tags) ? implode('.', $tags) : $tags;

            $result[] = new TaskDTO(
                $task['id'],
                $task['general'],
                $task['importance'] === 'HIGH',
                $department,
                $serviceClass,
                $supportService,
                $tags,
                $taskName
            );
        }

        return $result;
    }

    /**
     * Получить историю изменения статусов задачи
     * @param TaskDTO $taskDTO
     * @param int $sleepBetweenRequest
     * @return Status[]
     */
    public function getTaskStatusHistory(TaskDTO $taskDTO, int $sleepBetweenRequest = 0): array
    {
        $pageCurrent = 1;
        $pageSize = 100;

        $response = $this->getTaskStatusesResponse($taskDTO->getId(), $pageCurrent, $pageSize);

        $totalCount = $response['actions']['@attributes']['totalCount'];
        $actions = $response['actions']['action'];

        $countPages = ceil($totalCount / $pageSize) - 1;

        for ($i = 0; $i < $countPages; $i++) {
            if ($sleepBetweenRequest) {
                sleep($sleepBetweenRequest);
            }

            $pageCurrent = $i + 2;
            $response = $this->getTaskStatusesResponse($taskDTO->getId(), $pageCurrent, $pageSize);
            $actions = ArrayHelper::merge($actions, $response['actions']['action']);
        }

        if (array_key_exists('id', $actions)) {
            $actions = [$actions];
        }

        $statuses = [];
        foreach ($actions as $item) {
            $action = Action::getInstance($item);

            if (!$action->isActionChangeStatus()) {
                continue;
            }

            $actionId = $action->getId();
            $taskId = $taskDTO->getId();
            $taskNumId = $taskDTO->getNumId();
            $emergency = $taskDTO->isEmergency();
            $department = $taskDTO->getDepartment();
            $serviceClass = $taskDTO->getServiceClass();
            $supportService = $taskDTO->getSupportService();
            $tags = $taskDTO->getTags();
            $taskName = $taskDTO->getTaskName();

            if ($action->getType()->isTaskCreated()) {
                $type = new Type(Type::NEW);
            } elseif ($action->getType()->isStatusChange()) {
                $type = new Type($item['statusChange']['oldStatus']);

                $statuses[] = new Status(
                    $actionId,
                    $type,
                    $taskId,
                    $taskNumId,
                    $emergency,
                    DateTime::createFromFormat('d-m-Y H:i', $item['dateTime']),
                    $department,
                    $serviceClass,
                    $supportService,
                    $tags,
                    $taskName
                );

                $type = new Type($item['statusChange']['newStatus']);
            } elseif ($action->getType()->isTaskChanged()) {
                $emergency = $action->isEmergency();
            }

            $statuses[] = new Status(
                $actionId,
                $type,
                $taskId,
                $taskNumId,
                $emergency,
                $action->getDate(),
                $department,
                $serviceClass,
                $supportService,
                $tags,
                $taskName
            );
        }

        return $statuses;
    }

    private function getTaskStatusesResponse(int $taskId, int $page, int $pageSize): array
    {
        $response = $this->apiPlanfix->sendRequest(
            ApiPlanfix::METHOD_ACTION_GET_LIST,
            [
                'task' => ['id' => $taskId],
                'pageCurrent' => $page,
                'pageSize' => $pageSize,
                'sort' => 'asc',
            ]
        );

        if (!$this->apiPlanfix->isResponseOk($response)) {
            throw new RuntimeException('API response: ' . json_encode($response));
        }

        return $response;
    }

    /**
     * Получает general task ids по фильтрам
     *
     * @param int $filter
     * @return array
     * @throws PlanfixErrorException
     */
    public function getTaskIdsByFilter(int $filter): array
    {
        ini_set('max_execution_time', 0);
        set_time_limit(0);

        $pageCurrent = 0;

        $taskIdsToFilter = [];

        while (++$pageCurrent) {

            sleep(1);

            $response = $this->apiPlanfix->sendRequest(
                ApiPlanfix::METHOD_TASK_LIST,
                [
                    'pageCurrent' => $pageCurrent,
                    'sort' => 'NUMBER_ASC',
                    'pageSize' => 100,
                    'target' => $filter,
                ]
            );

            if ($response['tasks']['@attributes']['totalCount'] == 0 ||
                $response['tasks']['@attributes']['count'] == 0) {

                break;
            }

            if (!$this->apiPlanfix->isResponseOk($response)) {
                throw new PlanfixErrorException('API response: ' . json_encode($response));
            }

            $taskIdsToFilter = array_merge($taskIdsToFilter, array_column($response['tasks']['task'], 'id'));

        }

        return $taskIdsToFilter;
    }

    /**
     * @return mixed
     * @throws PlanfixErrorException
     */
    public function getTaskFilters()
    {
        $response = $this->apiPlanfix->sendRequest(
            ApiPlanfix::METHOD_TASK_GET_FILTER_LIST
        );

        if (!$this->apiPlanfix->isResponseOk($response)) {
            throw new PlanfixErrorException('API response: ' . json_encode($response));
        }

        return $response;
    }
}
