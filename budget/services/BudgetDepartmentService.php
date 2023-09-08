<?php

namespace app\modules\budget\services;

use app\modules\budget\models\BudgetDepartment;
use app\modules\budget\repositories\BudgetDepartmentRepository;
use app\modules\budget\repositories\exceptions\EntityDeleteException;
use app\modules\budget\repositories\exceptions\EntitySaveErrorException;
use app\modules\budget\resources\ResourceBudgetDepartment;
use app\modules\budget\services\exceptions\ServiceException;
use Exception;

class BudgetDepartmentService
{
    /** @var BudgetDepartmentRepository $budgetDepartments */
    private $budgetDepartments;

    /**
     * BudgetDepartmentService constructor.
     * @param BudgetDepartmentRepository $budgetDepartments
     */
    public function __construct(BudgetDepartmentRepository $budgetDepartments)
    {
        $this->budgetDepartments = $budgetDepartments;
    }

    /**
     * @param ResourceBudgetDepartment $resource
     * @return bool
     * @throws Exception
     */
    public function add(ResourceBudgetDepartment $resource): bool
    {
        try {
            $this->budgetDepartments->save(
                new BudgetDepartment([
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
     * @param BudgetDepartment $model
     * @return bool
     * @throws Exception
     */
    public function update(BudgetDepartment $model): bool
    {
        try {
            $this->budgetDepartments->save($model);
            return true;

        } catch (EntitySaveErrorException $e) {
            throw new ServiceException($e->getMessage());
        }
    }

    /**
     * @param BudgetDepartment $model
     * @return bool
     * @throws Exception
     */
    public function delete(BudgetDepartment $model): bool
    {
        try {
            $this->budgetDepartments->delete($model);
            return true;

        } catch (EntityDeleteException $e) {
            throw new ServiceException($e->getMessage());
        } catch (EntitySaveErrorException $e) {
            throw new ServiceException($e->getMessage());
        }
    }

    /**
     * @param ResourceBudgetDepartment[] $resourceBudgetDepartments
     * @return ResourceBudgetDepartment[]
     */
    public function extractNew(array $resourceBudgetDepartments): array
    {
        $result = [];
        $modelKeys = $this->budgetDepartments->getKeys();

        foreach ($resourceBudgetDepartments as $resource) {
            if (!in_array($resource->getKey(), $modelKeys)) {
                $result[] = $resource;
            }
        }

        return $result;
    }

    /**
     * @param ResourceBudgetDepartment[] $resources
     * @return BudgetDepartment[]
     */
    public function extractDelete(array $resources): array
    {
        $keysForDelete = [];
        foreach ($resources as $resource) {
            $keysForDelete[] = $resource->getKey();
        }

        $result = $this->budgetDepartments->getAll();
        foreach ($result as $key => $model) {

            if (in_array($model->key, $keysForDelete)) {
                unset($result[$key]);
            }
        }

        return $result;
    }

    /**
     * @param ResourceBudgetDepartment[] $resourceBudgetDepartments
     * @return array
     */
    public function extractUpdate(array $resourceBudgetDepartments): array
    {
        $result = [];
        $models = $this->budgetDepartments->getAll();

        foreach ($resourceBudgetDepartments as $resource) {
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
