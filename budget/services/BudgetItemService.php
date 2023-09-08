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
use app\modules\budget\services\exceptions\NoUniqueDbIndexException;
use app\modules\budget\services\exceptions\ServiceException;

class BudgetItemService
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
            /** @var BudgetItem $oldModel */
            $oldModel = $this->budgetItems->findItemByUniqueFields($model);

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
     * @param array $itemsFromResource
     * @return ResourceBudgetItem[]
     * @throws NoUniqueDbIndexException
     */
    public function extractNew(array $itemsFromResource): array
    {
        $items = [];

        $itemsFromDb = $this->budgetItems->getAll();

        foreach ($itemsFromResource as $itemFromResource) {

            $items[] = $itemFromResource;

            $repeatedItems = 0;

            foreach ($itemsFromDb as $itemFromDb) {

                if ($this->isEquals($itemFromResource, $itemFromDb)) {

                    $repeatedItems++;

                    if ($repeatedItems > 1) {
                        throw new NoUniqueDbIndexException();
                    }

                    array_pop($items);
                }
            }
        }

        return $items;
    }

    /**
     * @param array $resources
     * @return BudgetItem[]
     * @throws NoUniqueDbIndexException
     */
    public function extractDelete(array $resources): array
    {
        $itemsFromDb = $this->budgetItems->getAll();

        foreach ($resources as $itemFromResource) {

            $repeatedItems = 0;

            foreach ($itemsFromDb as $key => $itemFromDb) {

                if (!$this->isEquals($itemFromResource, $itemFromDb)) {
                    continue;
                }

                $repeatedItems++;

                if ($repeatedItems > 1) {
                    throw new NoUniqueDbIndexException();
                }

                unset($itemsFromDb[$key]);
            }
        }

        return $itemsFromDb;
    }

    /**
     * @param array $itemsFromResource
     * @return array
     * @throws NoUniqueDbIndexException
     */
    public function extractUpdate(array $itemsFromResource): array
    {
        $items = [];

        $itemsFromDb = $this->budgetItems->getAll();

        foreach ($itemsFromResource as $itemFromResource) {

            $repeatedItems = 0;

            foreach ($itemsFromDb as $itemFromDb) {
                if (!$this->isEquals($itemFromResource, $itemFromDb)) {
                    continue;
                }

                $repeatedItems++;

                if ($repeatedItems > 1) {
                    throw new NoUniqueDbIndexException();
                }

                if ($itemFromResource->getValue() === $itemFromDb['value']) {
                    continue;
                }

                // items are filled if item matches by unique fields and not mathes by value
                $items[] = ['itemFromResource' => $itemFromResource, 'itemFromDb' => $itemFromDb];

            }
        }

        return $items;
    }

    /**
     * @param ResourceBudgetItem $itemFromResource
     * @param BudgetItem $itemFromDb
     * @return bool
     */
    public function isEquals(ResourceBudgetItem $itemFromResource, BudgetItem $itemFromDb): bool
    {
        return
            $itemFromResource->getDepartmentCode() === $itemFromDb->departmentCode &&
            $itemFromResource->getCostItemCode() === $itemFromDb->costItemCode &&
            $itemFromResource->getYear() === $itemFromDb->year &&
            $itemFromResource->getMonth() === $itemFromDb->month;
    }
}
