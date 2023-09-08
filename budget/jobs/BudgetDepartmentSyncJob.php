<?php

namespace app\modules\budget\jobs;

use app\modules\budget\models\BudgetDepartment;
use app\modules\budget\providers\contracts\BudgetDepartmentProviderInterface;
use app\modules\budget\resources\ResourceBudgetDepartment;
use app\modules\budget\services\BudgetDepartmentService;
use Throwable;
use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\RetryableJobInterface;

class BudgetDepartmentSyncJob extends BaseObject implements JobInterface, RetryableJobInterface
{
    const TIME_REPEAT = 600;

    /** @var BudgetDepartmentProviderInterface */
    private $budgetDepartmentProvider;

    /** @var BudgetDepartmentService */
    private $budgetDepartmentService;

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->budgetDepartmentProvider = Yii::$container->get(BudgetDepartmentProviderInterface::class);
        $this->budgetDepartmentService = Yii::$container->get(BudgetDepartmentService::class);

    }

    /**
     * @inheritDoc
     */
    public function execute($queue)
    {
        $resources = $this->budgetDepartmentProvider->getAll();

        $forAdd = $this->budgetDepartmentService->extractNew($resources);
        $forUpdate = $this->budgetDepartmentService->extractUpdate($resources);
        $forDelete = $this->budgetDepartmentService->extractDelete($resources);

        foreach ($forAdd as $item) {
            $this->budgetDepartmentService->add($item);
        }

        foreach ($forUpdate as $code => $item) {
            /** @var BudgetDepartment $model */
            $model = $item['model'];

            /** @var ResourceBudgetDepartment $resource */
            $resource = $item['resource'];

            $model->name = $resource->getName();

            $this->budgetDepartmentService->update($model);
        }

        foreach ($forDelete as $item) {
            $this->budgetDepartmentService->delete($item);
        }
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