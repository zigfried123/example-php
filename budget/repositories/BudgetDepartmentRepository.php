<?php

namespace app\modules\budget\repositories;

use app\modules\budget\models\BudgetDepartment;
use app\modules\budget\repositories\exceptions\EntityDeleteException;
use app\modules\budget\repositories\exceptions\EntitySaveErrorException;

/**
 * Class BudgetDepartmentRepository
 * @package app\modules\budget\repositories
 */
class BudgetDepartmentRepository
{
    /** @var BudgetDepartment $budgetDepartment */
    private $budgetDepartment;

    /**
     * BudgetDepartmentRepository constructor.
     * @param BudgetDepartment $budgetDepartment
     */
    public function __construct(BudgetDepartment $budgetDepartment)
    {
        $this->budgetDepartment = $budgetDepartment;
    }

    /**
     * @param BudgetDepartment $model
     * @return bool
     * @throws EntitySaveErrorException
     */
    public function save(BudgetDepartment $model): bool
    {
        if (!$model->save()) {
            throw new EntitySaveErrorException(
                'Management period model dont saved' . json_encode(
                    $model->errors,
                    JSON_UNESCAPED_UNICODE
                )
            );
        }
        return true;
    }

    /**
     * @param BudgetDepartment $model
     * @return bool
     * @throws EntityDeleteException
     * @throws EntitySaveErrorException
     */
    public function delete(BudgetDepartment $model): bool
    {
        $model->is_deleted = true;

        if (!$this->save($model)) {
            throw new EntityDeleteException('Budget department model dont soft deleted');
        }

        return true;
    }

    /**
     * @return BudgetDepartment[]
     */
    public function getAll(): array
    {
        return BudgetDepartment::find()->all();
    }

    /**
     * @return array
     */
    public function getKeys(): array
    {
        $data = $this->budgetDepartment::find()->select(['key'])->asArray()->all();

        $result = [];
        foreach ($data as $item) {
            $result[] = $item['key'];
        }

        return $result;
    }
}
