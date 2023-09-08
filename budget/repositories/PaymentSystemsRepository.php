<?php

namespace app\modules\budget\repositories;

use app\modules\budget\models\PaymentSystem;
use app\modules\budget\repositories\exceptions\EntityDeleteException;
use app\modules\budget\repositories\exceptions\EntitySaveErrorException;

/**
 * Class BudgetDepartmentRepository
 * @package app\modules\budget\repositories
 */
class PaymentSystemsRepository
{
    /**
     * @var PaymentSystem  $paymentSystem
     */
    private $paymentSystem;

    /**
     * PaymentSystemsRepository constructor.
     * @param PaymentSystem $paymentSystem
     */
    public function __construct(PaymentSystem $paymentSystem)
    {
        $this->paymentSystem = $paymentSystem;
    }

    /**
     * @param PaymentSystem $model
     * @return bool
     * @throws EntitySaveErrorException
     */
    public function save(PaymentSystem $model): bool
    {
        if (!$model->save()) {
            throw new EntitySaveErrorException(
                'Payment system model dont saved' . json_encode(
                    $model->errors,
                    JSON_UNESCAPED_UNICODE
                )
            );
        }
        return true;
    }

    /**
     * @param PaymentSystem $model
     * @return bool
     * @throws EntityDeleteException
     * @throws EntitySaveErrorException
     */
    public function delete(PaymentSystem $model): bool
    {
        $model->is_deleted = true;

        if (!$this->save($model)) {
            throw new EntityDeleteException('Payment system model dont soft deleted');
        }

        return true;
    }

    /**
     * @return PaymentSystem[]
     */
    public function getAll(): array
    {
        return $this->paymentSystem::find()->all();
    }

    /**
     * @return array
     */
    public function getNames(): array
    {
        $data = $this->paymentSystem::find()->select(['name'])->asArray()->all();

        $result = [];
        foreach ($data as $item) {
            $result[] = $item['name'];
        }

        return $result;
    }

    /**
     * @return string[]
     */
    public function getKeys(): array
    {
        $data = $this->paymentSystem::find()->select(['key'])->asArray()->all();

        $result = [];
        foreach ($data as $item) {
            $result[] = $item['key'];
        }

        return $result;
    }
}
