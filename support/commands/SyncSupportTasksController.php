<?php

namespace app\modules\support\commands;

use app\components\filters\UniqueAccess;

use app\modules\support\services\PlanfixTasksService;
use app\modules\support\services\SyncTasksService;
use DateInterval;
use DateTime;
use Yii;
use yii\base\InvalidConfigException;
use yii\console\Controller;
use yii\db\Exception;
use yii\di\NotInstantiableException;
use yii\helpers\Console;

class SyncSupportTasksController extends Controller
{
    const SLEEP_SECOND_BETWEEN_REQUESTS = 1;
    const START_DATE_MONITORING = '2017-01-01 00:00:01'; // начиная с какой даты будет производится мониторинг задач

    public function behaviors()
    {
        return [
            'UniqueAccess' => [
                'class' => UniqueAccess::class,
            ],
        ];
    }

    /**
     * Синхноризация статусов задач ТП из planfix
     *
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotInstantiableException
     */
    public function actionRun()
    {
        /** @var PlanfixTasksService $planfixTasksService */
        $planfixTasksService = Yii::$container->get(PlanfixTasksService::class);

        /** @var SyncTasksService $syncTasksService */
        $syncTasksService = Yii::$container->get(SyncTasksService::class);

        $maxCreatedDate = $syncTasksService->getLastDateCreated() ?? self::START_DATE_MONITORING;
        $maxCreatedDate = DateTime::createFromFormat('Y-m-d H:i:s', $maxCreatedDate)
            ->sub(new DateInterval('P10D')); //берем с запасом

        Console::output('Поиск всех задач техподдержки начиная с ' . $maxCreatedDate->format('Y-m-d H:i:s'));

        $planfixTasks = $planfixTasksService->getTasks(
            $maxCreatedDate,
            self::SLEEP_SECOND_BETWEEN_REQUESTS
        );

        $countTasks = count($planfixTasks);

        Console::output('Найдено задач: ' . $countTasks);

        if (!$countTasks) {
            return;
        }

        Console::output('Обновление статусов задач');

        foreach ($planfixTasks as $index => $planfixTask) {
            Console::updateProgress(
                $index + 1,
                $countTasks, 'Задача: ' . $planfixTask->getId() . '/' . $planfixTask->getNumId()
            );

            $actions = $planfixTasksService->getTaskStatusHistory(
                $planfixTask,
                self::SLEEP_SECOND_BETWEEN_REQUESTS
            );
            $syncTasksService->save($actions);
        }

        Console::endProgress();
    }
}
