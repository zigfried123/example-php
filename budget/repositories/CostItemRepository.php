<?php

namespace app\modules\budget\repositories;

use app\modules\budget\models\CostItem;
use app\modules\budget\repositories\exceptions\EntityDeleteException;
use app\modules\budget\repositories\exceptions\EntitySaveErrorException;

/**
 * Class BudgetDepartmentRepository
 * @package app\modules\budget\repositories
 */
class CostItemRepository
{
    /**
     * @var CostItem $costItem
     */
    private $costItem;

    public function __construct(CostItem $costItem)
    {
        $this->costItem = $costItem;
    }

    /**
     * @param CostItem $model
     * @return bool
     * @throws EntitySaveErrorException
     */
    public function save(CostItem $model): bool
    {
        if (!$model->save()) {
            throw new EntitySaveErrorException(
                'Cost item model dont saved' . json_encode(
                    $model->errors,
                    JSON_UNESCAPED_UNICODE
                )
            );
        }
        return true;
    }

    /**
     * @param CostItem $model
     * @return bool
     * @throws EntityDeleteException
     * @throws EntitySaveErrorException
     */
    public function delete(CostItem $model): bool
    {
        $model->is_deleted = true;

        if (!$this->save($model)) {
            throw new EntityDeleteException('Cost item model dont soft deleted');
        }

        return true;
    }

    /**
     * @return CostItem[]
     */
    public function getAll(): array
    {
        return $this->costItem::find()->all();
    }

    /**
     * @return array
     */
    public function getKeys(): array
    {
        $data = $this->costItem::find()->select(['key'])->asArray()->all();

        $result = [];
        foreach ($data as $item) {
            $result[] = $item['key'];
        }

        return $result;
    }
}
