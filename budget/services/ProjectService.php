<?php

namespace app\modules\budget\services;

use app\modules\budget\models\Project;
use app\modules\budget\repositories\exceptions\EntityDeleteException;
use app\modules\budget\repositories\exceptions\EntitySaveErrorException;
use app\modules\budget\repositories\ProjectRepository;
use app\modules\budget\resources\ResourceProject;
use app\modules\budget\services\exceptions\ServiceException;

class ProjectService
{
    /**
     * @var ProjectRepository $projects
     */
    private $projects;

    /**
     * ProjectService constructor.
     * @param ProjectRepository $projects
     */
    public function __construct(ProjectRepository $projects)
    {
        $this->projects = $projects;
    }

    /**
     * @param ResourceProject $resource
     * @return bool
     * @throws ServiceException
     */
    public function add(ResourceProject $resource): bool
    {
        try {
            $this->projects->save(
                new Project([
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
     * @param Project $model
     * @return bool
     * @throws ServiceException
     */
    public function update(Project $model): bool
    {
        try {
            $this->projects->save($model);
            return true;

        } catch (EntitySaveErrorException $e) {
            throw new ServiceException($e->getMessage());
        }
    }

    /**
     * @param Project $model
     * @return bool
     * @throws ServiceException
     */
    public function delete(Project $model): bool
    {
        try {
            $this->projects->delete($model);
            return true;

        } catch (EntityDeleteException $e) {
            throw new ServiceException($e->getMessage());

        } catch (EntitySaveErrorException $e) {
            throw new ServiceException($e->getMessage());
        }
    }

    /**
     * @param ResourceProject[] $resources
     * @return ResourceProject[]
     */
    public function extractNew(array $resources): array
    {
        $result = [];
        $modelKeys = $this->projects->getKeys();
        foreach ($resources as $resource) {
            if (!in_array($resource->getKey(), $modelKeys)) {
                $result[] = $resource;
            }
        }

        return $result;
    }

    /**
     * @param ResourceProject[] $resources
     * @return Project[]
     */
    public function extractDelete(array $resources): array
    {
        $keysForDelete = [];
        foreach ($resources as $resource) {
            $keysForDelete[] = $resource->getKey();
        }

        $result = $this->projects->getAll();
        foreach ($result as $key => $model) {

            if (in_array($model->key, $keysForDelete)) {
                unset($result[$key]);
            }
        }

        return $result;
    }

    /**
     * @param ResourceProject[] $resources
     * @return array
     */
    public function extractUpdate(array $resources): array
    {
        $result = [];
        $models = $this->projects->getAll();

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
