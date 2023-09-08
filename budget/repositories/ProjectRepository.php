<?php

namespace app\modules\budget\repositories;

use app\modules\budget\models\Project;
use app\modules\budget\repositories\exceptions\EntityDeleteException;
use app\modules\budget\repositories\exceptions\EntitySaveErrorException;

/**
 * Class BudgetDepartmentRepository
 * @package app\modules\budget\repositories
 */
class ProjectRepository
{
    /**
     * @var Project $project
     */
    private $project;

    /**
     * ProjectRepository constructor.
     * @param Project $project
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    /**
     * @param Project $model
     * @return bool
     * @throws EntitySaveErrorException
     */
    public function save(Project $model): bool
    {
        if (!$model->save()) {
            throw new EntitySaveErrorException(
                'Project period model dont saved' . json_encode(
                    $model->errors,
                    JSON_UNESCAPED_UNICODE
                )
            );
        }
        return true;
    }

    /**
     * @param Project $model
     * @return bool
     * @throws EntityDeleteException
     * @throws EntitySaveErrorException
     */
    public function delete(Project $model): bool
    {
        $model->is_deleted = true;

        if (!$this->save($model)) {
            throw new EntityDeleteException('Project model dont soft deleted');
        }

        return true;
    }

    /**
     * @return Project[]
     */
    public function getAll(): array
    {
        return $this->project::find()->all();
    }

    /**
     * @return string[]
     */
    public function getKeys(): array
    {
        $data = $this->project::find()->select(['key'])->asArray()->all();

        $result = [];
        foreach ($data as $item) {
            $result[] = $item['key'];
        }

        return $result;
    }
}
