<?php

namespace app\modules\budget\jobs;

use app\modules\budget\providers\contracts\ManagementPeriodProviderInterface;
use app\modules\budget\services\ManagementPeriodService;
use Throwable;
use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\RetryableJobInterface;

class ManagementPeriodSyncJob extends BaseObject implements JobInterface, RetryableJobInterface
{
    const TIME_REPEAT = 600;

    /** @var ManagementPeriodProviderInterface */
    private $managementPeriodProvider;

    /** @var ManagementPeriodService */
    private $managementPeriodService;

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->managementPeriodProvider = Yii::$container->get(ManagementPeriodProviderInterface::class);
        $this->managementPeriodService = Yii::$container->get(ManagementPeriodService::class);
    }

    /**
     * @inheritDoc
     */
    public function execute($queue)
    {
        $resources = $this->managementPeriodProvider->getAll();

        $forAdd = $this->managementPeriodService->extractNew($resources);
        $forDelete = $this->managementPeriodService->extractDelete($resources);

        foreach ($forAdd as $item) {
            $this->managementPeriodService->add($item);
        }

        foreach ($forDelete as $item) {
            $this->managementPeriodService->delete($item);
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
