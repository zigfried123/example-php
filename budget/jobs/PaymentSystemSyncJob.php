<?php

namespace app\modules\budget\jobs;

use app\modules\budget\providers\contracts\PaymentSystemProviderInterface;
use app\modules\budget\services\PaymentSystemService;
use Throwable;
use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\RetryableJobInterface;

class PaymentSystemSyncJob extends BaseObject implements JobInterface, RetryableJobInterface
{
    const TIME_REPEAT = 600;

    /** @var PaymentSystemProviderInterface */
    private $paymentSystemProvider;

    /** @var PaymentSystemService */
    private $paymentSystemService;

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->paymentSystemProvider = Yii::$container->get(PaymentSystemProviderInterface::class);
        $this->paymentSystemService = Yii::$container->get(PaymentSystemService::class);
    }

    /**
     * @inheritDoc
     */
    public function execute($queue)
    {
        $resources = $this->paymentSystemProvider->getAll();

        $forAdd = $this->paymentSystemService->extractNew($resources);
        $forDelete = $this->paymentSystemService->extractDelete($resources);

        foreach ($forAdd as $item) {
            $this->paymentSystemService->add($item);
        }

        foreach ($forDelete as $item) {
            $this->paymentSystemService->delete($item);
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
