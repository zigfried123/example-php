<?php

namespace app\modules\budget\services;

use app\modules\budget\models\ManagementPeriod;
use app\modules\budget\repositories\exceptions\EntityDeleteException;
use app\modules\budget\repositories\exceptions\EntitySaveErrorException;
use app\modules\budget\repositories\ManagementPeriodRepository;
use app\modules\budget\resources\ResourceManagementPeriod;
use app\modules\budget\services\exceptions\ServiceException;

class ManagementPeriodService
{
    /**
     * @var ManagementPeriodRepository $managementPeriod
     */
    private $managementPeriod;

    /**
     * ManagementPeriodService constructor.
     * @param ManagementPeriodRepository $managementPeriod
     */
    public function __construct(ManagementPeriodRepository $managementPeriod)
    {
        $this->managementPeriod = $managementPeriod;
    }

    /**
     * @param ResourceManagementPeriod $resource
     * @return bool
     * @throws ServiceException
     */
    public function add(ResourceManagementPeriod $resource): bool
    {
        try {
            $this->managementPeriod->save(
                new ManagementPeriod([
                    'key' => $resource->getKey(),
                    'date' => $resource->getDate(),
                    'name' => $resource->getName()
                ]));
            return true;
        } catch (EntitySaveErrorException $e) {
            throw new ServiceException($e->getMessage());
        }
    }

    /**
     * @param ManagementPeriod $model
     * @return bool
     * @throws ServiceException
     */
    public function update(ManagementPeriod $model): bool
    {
        try {
            $this->managementPeriod->save($model);
            return true;

        } catch (EntitySaveErrorException $e) {
            throw new ServiceException($e->getMessage());
        }
    }

    /**
     * @param ManagementPeriod $model
     * @return bool
     * @throws ServiceException
     */
    public function delete(ManagementPeriod $model): bool
    {
        try {
            $this->managementPeriod->delete($model);
            return true;

        } catch (EntityDeleteException $e) {
            throw new ServiceException($e->getMessage());

        } catch (EntitySaveErrorException $e) {
            throw new ServiceException($e->getMessage());
        }
    }

    /**
     * @param ResourceManagementPeriod[] $resources
     * @return array
     */
    public function extractNew(array $resources): array
    {
        $result = [];
        $modelKeys = $this->managementPeriod->getKeys();
        foreach ($resources as $resource) {
            if (!in_array($resource->getKey(), $modelKeys)) {
                $result[] = $resource;
            }
        }

        return $result;
    }

    /**
     * @param ResourceManagementPeriod[] $resources
     * @return array
     */
    public function extractDelete(array $resources): array
    {
        $keysForDelete = [];
        foreach ($resources as $resource) {
            $keysForDelete[] = $resource->getKey();
        }

        $result = $this->managementPeriod->getAll();
        foreach ($result as $key => $model) {

            if (in_array($model->key, $keysForDelete)) {
                unset($result[$key]);
            }
        }

        return $result;
    }

    /**
     * @param ResourceManagementPeriod[] $resources
     * @return array
     */
    public function extractUpdate(array $resources): array
    {
        $result = [];
        $models = $this->managementPeriod->getAll();

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
