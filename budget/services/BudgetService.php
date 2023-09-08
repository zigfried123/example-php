<?php

namespace app\modules\budget\services;

use app\modules\budget\events\DeleteBudgetItemEvent;
use app\modules\budget\events\NewBudgetItemEvent;
use app\modules\budget\events\UpdateBudgetItemEvent;
use app\modules\budget\models\BudgetItem;
use app\modules\budget\repositories\BudgetItemRepository;
use app\modules\budget\repositories\exceptions\EntityDeleteException;
use app\modules\budget\repositories\exceptions\EntitySaveErrorException;
use app\modules\budget\resources\ResourceBudgetItem;
use app\modules\budget\services\exceptions\ServiceException;

class BudgetService
{
    /**
     * @var BudgetItemRepository
     */
    private $budgetItems;

    /**
     * BudgeService constructor.
     * @param BudgetItemRepository $budgetItems
     */
    public function __construct(BudgetItemRepository $budgetItems)
    {
        $this->budgetItems = $budgetItems;
    }

    /**
     * @param ResourceBudgetItem $resource
     * @return bool
     * @throws ServiceException
     */
    public function add(ResourceBudgetItem $resource): bool
    {
        try {
            $model = new BudgetItem(
                [
                    'departmentCode' => $resource->getDepartmentCode(),
                    'costItemCode' => $resource->getCostItemCode(),
                    'year' => $resource->getYear(),
                    'month' => $resource->getMonth(),
                    'value' => $resource->getValue()
                ]
            );

            $this->budgetItems->save($model);

            $event = new NewBudgetItemEvent($model);
            $model->trigger(BudgetItem::EVENT_NEW_BUDGET_ITEM, $event);

        } catch (EntitySaveErrorException $e) {
            throw new ServiceException($e->getMessage());
        }

        return true;
    }

    /**
     * @param BudgetItem $model
     * @return bool
     * @throws ServiceException
     */
    public function update(BudgetItem $model): bool
    {
        try {

            $oldModel = $this->budgetItems->findItemByUniqueFields($model);

            /**
             * @var BudgetItem $oldModel
             */

            $this->budgetItems->save($model);

            $event = new UpdateBudgetItemEvent($model, $oldModel);
            $model->trigger(BudgetItem::EVENT_UPDATE_BUDGET_ITEM, $event);

        } catch (EntitySaveErrorException $e) {
            throw new ServiceException($e->getMessage());
        }

        return true;
    }

    /**
     * @param BudgetItem $model
     * @return bool
     * @throws ServiceException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function delete(BudgetItem $model): bool
    {
        try {
            $this->budgetItems->delete($model);

            $event = new DeleteBudgetItemEvent($model);
            $model->trigger(BudgetItem::EVENT_DELETE_BUDGET_ITEM, $event);
            
        } catch (EntityDeleteException $e) {
            throw new ServiceException($e->getMessage());
        }

        return true;
    }

    /**
     * @param ResourceBudgetItem[] $itemsFromResource
     * @return ResourceBudgetItem[]
     */
    public function extractNew(array $itemsFromResource): array
    {
        $items = [];

        $itemsFromDb = $this->budgetItems->getAll();

        foreach ($itemsFromResource as $itemFromResource) {
            foreach ($itemsFromDb as $itemFromDb) {
                if ($this->isEquals($itemFromResource, $itemFromDb)) continue;

                $items[] = $itemFromResource;
            }
        }

        return $items;
    }

    /**
     * @param ResourceBudgetItem[] $resources
     * @return BudgetItem[]
     */
    public function extractDelete(array $resources): array
    {
        $itemsFromDb = $this->budgetItems->getAll();

        foreach ($resources as $itemFromResource) {
            foreach ($itemsFromDb as $key => $itemFromDb) {
                if (!$this->isEquals($itemFromResource, $itemFromDb)) continue;

                unset($itemsFromDb[$key]);
            }
        }

        return $itemsFromDb;
    }

    /**
     * @param ResourceBudgetItem[] $itemsFromResource
     * @return array
     */
    public function extractUpdate(array $itemsFromResource): array
    {
        $items = [];

        $itemsFromDb = $this->budgetItems->getAll();

        foreach ($itemsFromResource as $itemFromResource) {
            foreach ($itemsFromDb as $itemFromDb) {
                if (!$this->isEquals($itemFromResource, $itemFromDb)) continue;

                if ($itemFromResource->getValue() === $itemFromDb['value']) continue;

                $items[$itemFromDb['departmentCode']][$itemFromDb['costItemCode']][$itemFromDb['year']][$itemFromDb['month']] = ['itemFromResource' => $itemFromResource, 'itemFromDb' => $itemFromDb];

            }
        }

        return $items;
    }

    /**
     * @param ResourceBudgetItem $itemFromResource
     * @param BudgetItem $itemFromDb
     * @return bool
     */
    private function isEquals(ResourceBudgetItem $itemFromResource, BudgetItem $itemFromDb): bool
    {
        return
            $itemFromResource->getDepartmentCode() === $itemFromDb->departmentCode &&
            $itemFromResource->getCostItemCode() === $itemFromDb->costItemCode &&
            $itemFromResource->getYear() === $itemFromDb->year &&
            $itemFromResource->getMonth() === $itemFromDb->month;
    }

}
