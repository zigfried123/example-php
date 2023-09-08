<?php

namespace app\modules\budget\repositories;

use app\modules\budget\models\BudgetItem;
use app\modules\budget\repositories\exceptions\EntityDeleteException;
use app\modules\budget\repositories\exceptions\EntitySaveErrorException;
use yii\db\ActiveRecord;

/**
 * Class BudgetItemRepository
 * @package app\modules\budget\repositories
 */
class BudgetItemRepository
{
    /** @var BudgetItem $budgetItem */
    private $budgetItem;

    /**
     * BudgetItemRepository constructor.
     * @param BudgetItem $budgetItem
     */
    public function __construct(BudgetItem $budgetItem)
    {
        $this->budgetItem = $budgetItem;
    }

    /**
     * @param BudgetItem $model
     * @return bool
     * @throws EntitySaveErrorException
     */
    public function save(BudgetItem $model): bool
    {
        if (!$model->save()) {
            throw new EntitySaveErrorException(
                'Budget item model dont saved' . json_encode(
                    $model->errors,
                    JSON_UNESCAPED_UNICODE
                )
            );
        }

        return true;
    }

    /**
     * @param BudgetItem $model
     * @return bool
     * @throws EntityDeleteException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function delete(BudgetItem $model): bool
    {
        if (!$model->delete()) {
            throw new EntityDeleteException('Budget item model dont deleted');
        }

        return true;
    }

    /**
     * @return BudgetItem[]
     */
    public function getAll(): array
    {
        return $this->budgetItem::find()->all();
    }

    /**
     * @param BudgetItem $model
     * @return array|ActiveRecord|null
     */
    public function findItemByUniqueFields(BudgetItem $model)
    {
        return $this->budgetItem::find()->where([
            'departmentCode' => $model->departmentCode,
            'costItemCode' => $model->costItemCode,
            'year' => $model->year,
            'month' => $model->month
        ])->one();
    }

}
