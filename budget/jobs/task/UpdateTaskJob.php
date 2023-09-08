<?php

namespace app\modules\budget\jobs\task;

use app\modules\budget\providers\contracts\BudgetTaskProviderInterface;
use app\modules\budget\repositories\TaskRepository;
use app\modules\budget\services\TaskService;
use Throwable;
use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\RetryableJobInterface;

class UpdateTaskJob extends BaseObject implements JobInterface, RetryableJobInterface
{
    const TIME_REPEAT = 600;

    /** @var int */
    public $id;

    /** @var TaskService */
    private $taskService;

    /** @var TaskRepository */
    private $tasks;

    /** @var BudgetTaskProviderInterface */
    private $planfixBudgetTaskProvider;

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->planfixBudgetTaskProvider = Yii::$container->get(BudgetTaskProviderInterface::class);
        $this->taskService = Yii::$container->get(TaskService::class);
        $this->tasks = Yii::$container->get(TaskRepository::class);
    }

    /**
     * @inheritDoc
     */
    public function execute($queue)
    {
        $resourceBudgetTask = $this->planfixBudgetTaskProvider->get($this->id);
        $task = $this->tasks->getByTaskId($this->id);

        $task->task_id = $resourceBudgetTask->getId();
        $task->status = $resourceBudgetTask->getStatus();
        $task->owner_id = $resourceBudgetTask->getOwnerId();
        $task->task_created = $resourceBudgetTask->getCreated()->format('Y-m-d H:i:s');
        $task->general = $resourceBudgetTask->getGeneral();
        $task->department = $resourceBudgetTask->getBudgetDepartmentValue();
        $task->cost_item = $resourceBudgetTask->getCostItemValue();
        $task->currency = $resourceBudgetTask->getCurrencyValue();
        $task->management_period = $resourceBudgetTask->getManagementPeriodValue();
        $task->payment_system = $resourceBudgetTask->getPaymentSystemValue();
        $task->project = $resourceBudgetTask->getProjectValue();
        $task->amount_paid = $resourceBudgetTask->getAmountPaid();
        $task->amount_in_currency = $resourceBudgetTask->getAmountInCurrency();
        $task->amount_in_rub = $resourceBudgetTask->getAmountInRub();

        $this->taskService->update($task);
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
