<?php

namespace app\modules\budget\commands;

use app\components\filters\UniqueAccess;
use app\modules\budget\jobs\BudgetDepartmentSyncJob;
use app\modules\budget\jobs\CostItemSyncJob;
use app\modules\budget\jobs\CurrencySyncJob;
use app\modules\budget\jobs\ManagementPeriodSyncJob;
use app\modules\budget\jobs\PaymentSystemSyncJob;
use app\modules\budget\jobs\ProjectSyncJob;
use Yii;
use yii\console\Controller;

class PlanfixController extends Controller
{
    /** @var string[] */
    private $jobs = [
        BudgetDepartmentSyncJob::class,
        CostItemSyncJob::class,
        CurrencySyncJob::class,
        ManagementPeriodSyncJob::class,
        PaymentSystemSyncJob::class,
        ProjectSyncJob::class,
    ];

    public function behaviors()
    {
        return [
            'UniqueAccess' => [
                'class' => UniqueAccess::class,
            ]
        ];
    }

    /**
     * Синхноризация справочников  planfix
     */
    public function actionSyncHandbooks()
    {
        foreach ($this->jobs as $job) {
            Yii::$app->queue->push(new $job());
        }
    }
}
