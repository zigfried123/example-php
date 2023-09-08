<?php

namespace app\modules\budget\jobs;

use app\modules\budget\models\CostItem;
use app\modules\budget\providers\contracts\CostItemProviderInterface;
use app\modules\budget\resources\ResourceCostItem;
use app\modules\budget\services\CostItemService;
use Throwable;
use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\RetryableJobInterface;

class CostItemSyncJob extends BaseObject implements JobInterface, RetryableJobInterface
{
    const TIME_REPEAT = 600;

    /** @var CostItemProviderInterface */
    private $costItemProvider;

    /** @var CostItemService */
    private $costItemService;

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->costItemProvider = Yii::$container->get(CostItemProviderInterface::class);
        $this->costItemService = Yii::$container->get(CostItemService::class);
    }

    /**
     * @inheritDoc
     */
    public function execute($queue)
    {
        $resources = $this->costItemProvider->getAll();

        $forAdd = $this->costItemService->extractNew($resources);
        $forUpdate = $this->costItemService->extractUpdate($resources);
        $forDelete = $this->costItemService->extractDelete($resources);

        foreach ($forAdd as $item) {
            $this->costItemService->add($item);
        }

        foreach ($forUpdate as $code => $item) {
            /** @var CostItem $model */
            $model = $item['model'];

            /** @var ResourceCostItem $resource */
            $resource = $item['resource'];

            $model->name = $resource->getName();

            $this->costItemService->update($model);
        }

        foreach ($forDelete as $item) {
            $this->costItemService->delete($item);
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
