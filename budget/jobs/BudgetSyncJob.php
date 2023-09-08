<?php

namespace app\modules\budget\jobs;

use app\modules\budget\models\BudgetItem;
use app\modules\budget\providers\contracts\BudgetProviderInterface;
use app\modules\budget\resources\ResourceBudgetItem;
use app\modules\budget\services\BudgetItemService;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\RetryableJobInterface;
use Yii;
use Throwable;

class BudgetSyncJob extends BaseObject implements JobInterface, RetryableJobInterface
{
    const TIME_REPEAT = 600;

    /** @var BudgetProviderInterface */
    private $budgetProvider;

    /** @var BudgetItemService */
    private $budgetItemService;

    /** @var ResourceBudgetItem[] $resources */
    private $resources;

    /**
     * BudgetSyncJob constructor.
     * @param array $config
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->budgetProvider = Yii::$container->get(BudgetProviderInterface::class);
        $this->budgetItemService = Yii::$container->get(BudgetItemService::class);

        $this->resources = $this->budgetProvider->getAll();
    }

    /**
     * @param \yii\queue\Queue $queue
     * @throws Throwable
     * @throws \app\modules\budget\services\exceptions\ServiceException
     * @throws \yii\db\StaleObjectException
     */
    public function execute($queue): void
    {
        $this->add();
        $this->update();
        $this->delete();
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

    /**
     * @throws \app\modules\budget\services\exceptions\ServiceException
     */
    private function add(): void
    {
        $forAdd = $this->budgetItemService->extractNew($this->resources);

        foreach ($forAdd as $item) {
            $this->budgetItemService->add($item);
        }
    }

    /**
     * @throws \app\modules\budget\services\exceptions\ServiceException
     */
    private function update(): void
    {
        $forUpdate = $this->budgetItemService->extractUpdate($this->resources);

        foreach ($forUpdate as $item) {
            /** @var BudgetItem $model */
            $model = $item['model'];

            /** @var ResourceBudgetItem $resource */
            $resource = $item['resource'];

            $model->value = $resource->getValue();

            $this->budgetItemService->update($model);
        }
    }

    /**
     * @throws Throwable
     * @throws \app\modules\budget\services\exceptions\ServiceException
     * @throws \yii\db\StaleObjectException
     */
    private function delete(): void
    {
        $forDelete = $this->budgetItemService->extractDelete($this->resources);

        foreach ($forDelete as $item) {
            $this->budgetItemService->delete($item);
        }
    }
}
