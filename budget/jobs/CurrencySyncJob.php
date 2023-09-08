<?php

namespace app\modules\budget\jobs;

use app\modules\budget\models\Currency;
use app\modules\budget\providers\contracts\CurrencyProviderInterface;
use app\modules\budget\resources\ResourceCurrency;
use app\modules\budget\services\CurrencyService;
use Throwable;
use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\RetryableJobInterface;

class CurrencySyncJob extends BaseObject implements JobInterface, RetryableJobInterface
{
    const TIME_REPEAT = 600;

    /** @var CurrencyProviderInterface */
    private $currencyProvider;

    /** @var CurrencyService */
    private $currencyService;

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->currencyProvider = Yii::$container->get(CurrencyProviderInterface::class);
        $this->currencyService = Yii::$container->get(CurrencyService::class);
    }

    /**
     * @inheritDoc
     */
    public function execute($queue)
    {
        $resources = $this->currencyProvider->getAll();

        $forAdd = $this->currencyService->extractNew($resources);
        $forUpdate = $this->currencyService->extractUpdate($resources);
        $forDelete = $this->currencyService->extractDelete($resources);

        foreach ($forAdd as $item) {
            $this->currencyService->add($item);
        }

        foreach ($forUpdate as $code => $item) {
            /** @var Currency $model */
            $model = $item['model'];

            /** @var ResourceCurrency $resource */
            $resource = $item['resource'];

            $model->name = $resource->getName();

            $this->currencyService->update($model);
        }

        foreach ($forDelete as $item) {
            $this->currencyService->delete($item);
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
