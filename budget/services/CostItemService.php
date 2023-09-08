<?php

namespace app\modules\budget\services;

use app\modules\budget\models\CostItem;
use app\modules\budget\repositories\CostItemRepository;
use app\modules\budget\repositories\exceptions\EntityDeleteException;
use app\modules\budget\repositories\exceptions\EntitySaveErrorException;
use app\modules\budget\resources\ResourceCostItem;
use app\modules\budget\services\exceptions\ServiceException;

class CostItemService
{
    /**
     * @var CostItemRepository
     */
    private $costItems;

    /**
     * CostItemService constructor.
     * @param CostItemRepository $costItems
     */
    public function __construct(CostItemRepository $costItems)
    {
        $this->costItems = $costItems;
    }

    /**
     * @param ResourceCostItem $resource
     * @return bool
     * @throws ServiceException
     */
    public function add(ResourceCostItem $resource): bool
    {
        try {
            $this->costItems->save(
                new CostItem([
                    'key' => $resource->getKey(),
                    'code' => $resource->getCode(),
                    'name' => $resource->getName()
                ])
            );
            return true;
        } catch (EntitySaveErrorException $e) {
            throw new ServiceException($e->getMessage());
        }
    }

    /**
     * @param CostItem $model
     * @return bool
     * @throws ServiceException
     */
    public function update(CostItem $model): bool
    {
        try {
            $this->costItems->save($model);
            return true;

        } catch (EntitySaveErrorException $e) {
            throw new ServiceException($e->getMessage());
        }
    }

    /**
     * @param CostItem $model
     * @return bool
     * @throws ServiceException
     */
    public function delete(CostItem $model): bool
    {
        try {
            $this->costItems->delete($model);
            return true;

        } catch (EntityDeleteException $e) {
            throw new ServiceException($e->getMessage());

        } catch (EntitySaveErrorException $e) {
            throw new ServiceException($e->getMessage());
        }
    }

    /**
     * @param ResourceCostItem[] $resources
     * @return ResourceCostItem[]
     */
    public function extractNew(array $resources): array
    {
        $result = [];
        $modelKeys = $this->costItems->getKeys();
        foreach ($resources as $resource) {
            if (!in_array($resource->getKey(), $modelKeys)) {
                $result[] = $resource;
            }
        }

        return $result;
    }

    /**
     * @param ResourceCostItem[] $resources
     * @return CostItem[]
     */
    public function extractDelete(array $resources): array
    {
        $keysForDelete = [];
        foreach ($resources as $resource) {
            $keysForDelete[] = $resource->getKey();
        }

        $result = $this->costItems->getAll();
        foreach ($result as $key => $model) {

            if (in_array($model->key, $keysForDelete)) {
                unset($result[$key]);
            }
        }

        return $result;
    }

    /**
     * @param ResourceCostItem[] $resources
     * @return array
     */
    public function extractUpdate(array $resources): array
    {
        $result = [];
        $models = $this->costItems->getAll();

        foreach ($resources as $resource) {
            foreach ($models as $model) {
                if ($model->key === $resource->getKey()) {
                    if ($resource->getName() !== $model->name) {
                        $result[$resource->getKey()] = ['model' => $model, 'resource' => $resource];
                    }
                }
            }
        }
        return $result;
    }
}
