<?php

namespace app\modules\support\controllers;

use app\modules\support\services\FilterTasksService;
use app\modules\support\services\PlanfixTasksService;
use app\modules\support\services\SyncTasksService;
use DateTimeImmutable;
use Yii;
use yii\rest\Controller;
use yii\web\Response;

class ApiController extends Controller
{
    /** @var SyncTasksService */
    private $syncTasksService;

    /** @var PlanfixTasksService */
    private $planfixService;

    /** @var FilterTasksService */
    private $filterService;

    public function __construct(
        $id,
        $module,
        SyncTasksService $syncTasksService,
        PlanfixTasksService $planfixService,
        FilterTasksService $filterService,
        $config = []
    )
    {
        parent::__construct($id, $module, $config);

        $this->syncTasksService = $syncTasksService;
        $this->planfixService = $planfixService;
        $this->filterService = $filterService;
    }

    public function actionList(string $from = '2017-01-01', string $to = '2022-01-01')
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $supportTaskStatus = $this->syncTasksService->cfdAmountTasks(
            DateTimeImmutable::createFromFormat('Y-m-d', $from)->setTime(0, 0, 0),
            DateTimeImmutable::createFromFormat('Y-m-d', $to)->setTime(0, 0, 0)
        );

        $result = [];

        foreach ($supportTaskStatus as $item) {
            $statusType = $item->getStatusType();

            $result[] = [
                'task' => [
                    'id' => $item->task_id,
                    'num' => $item->task_num,
                ],
                'status' => [
                    'id' => $statusType->getCode(),
                    'title' => $statusType->getTitle()
                ],
                'emergency' => $item->emergency ? '1' : '0',
                'date' => $item->getDateCreated()->format('Y-m-d H:i:s'),
            ];
        }

        return $result;
    }

    /**
     * Получает задачи за период в json
     *
     * @param string $from
     * @param string $to
     * @param int|null $filter
     * @param bool $daysOff
     * @return array
     * @throws \app\common\exceptions\PlanfixErrorException
     * @throws \yii\db\Exception
     */
    public function actionGetTasksForPeriod(string $from, string $to, int $filter = null, bool $daysOff = true): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $tasks = $this->syncTasksService->getTasksForPeriod($from, $to, $daysOff);

        if (isset($filter)) {
            $tasks = $this->filterService->filterTasks($tasks, $filter);
        }

        return $tasks;
    }
}
