<?php

namespace app\modules\budget\jobs\task;

use app\modules\budget\models\Task;
use app\modules\budget\providers\contracts\BudgetTaskProviderInterface;
use app\modules\budget\services\TaskService;
use Throwable;
use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\RetryableJobInterface;

class AddTaskJob extends BaseObject implements JobInterface, RetryableJobInterface
{
    const TIME_REPEAT = 600;

    /** @var int */
    public $id;

    /** @var TaskService */
    private $taskService;

    /** @var BudgetTaskProviderInterface */
    private $planfixBudgetTaskProvider;

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->planfixBudgetTaskProvider = Yii::$container->get(BudgetTaskProviderInterface::class);
        $this->taskService = Yii::$container->get(TaskService::class);
    }

    /**
     * @inheritDoc
     */
    public function execute($queue)
    {
        $resourceBudgetTask = $this->planfixBudgetTaskProvider->get($this->id);

        $this->taskService->add(new Task([
            'task_id' => $resourceBudgetTask->getId(),
            'status' => $resourceBudgetTask->getStatus(),
            'owner_id' => $resourceBudgetTask->getOwnerId(),
            'task_created' => $resourceBudgetTask->getCreated()->format('Y-m-d H:i:s'),
            'general' => $resourceBudgetTask->getGeneral(),
            'department' => $resourceBudgetTask->getBudgetDepartmentValue(),
            'cost_item' => $resourceBudgetTask->getCostItemValue(),
            'currency' => $resourceBudgetTask->getCurrencyValue(),
            'management_period' => $resourceBudgetTask->getManagementPeriodValue(),
            'payment_system' => $resourceBudgetTask->getPaymentSystemValue(),
            'project' => $resourceBudgetTask->getProjectValue(),
            'amount_paid' => $resourceBudgetTask->getAmountPaid(),
            'amount_in_currency' => $resourceBudgetTask->getAmountInCurrency(),
            'amount_in_rub' => $resourceBudgetTask->getAmountInRub(),
        ]));
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
