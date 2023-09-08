<?php

namespace app\modules\budget\repositories;

use app\modules\budget\models\Currency;
use app\modules\budget\repositories\exceptions\EntityDeleteException;
use app\modules\budget\repositories\exceptions\EntitySaveErrorException;

/**
 * Class BudgetDepartmentRepository
 * @package app\modules\budget\repositories
 */
class CurrencyRepository
{
    /** @var Currency $currency */
    private $currency;

    /**
     * CurrencyRepository constructor.
     * @param Currency $currency
     */
    public function __construct(Currency $currency)
    {
        $this->currency = $currency;
    }

    /**
     * @param Currency $model
     * @return bool
     * @throws EntitySaveErrorException
     */
    public function save(Currency $model): bool
    {
        if (!$model->save()) {
            throw new EntitySaveErrorException(
                'Currency model dont saved' . json_encode(
                    $model->errors,
                    JSON_UNESCAPED_UNICODE
                )
            );
        }
        return true;
    }

    /**
     * @param Currency $model
     * @return bool
     * @throws EntityDeleteException
     * @throws EntitySaveErrorException
     */
    public function delete(Currency $model): bool
    {
        $model->is_deleted = true;

        if (!$this->save($model)) {
            throw new EntityDeleteException('Currency model dont soft deleted');
        }

        return true;
    }

    /**
     * @return Currency[]
     */
    public function getAll(): array
    {
        return $this->currency::find()->all();
    }

    /**
     * @return array
     */
    public function getKeys(): array
    {
        $data = $this->currency::find()->select(['key'])->asArray()->all();
        $result = [];
        foreach ($data as $item) {
            $result[] = $item['key'];
        }

        return $result;
    }
}
