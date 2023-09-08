<?php

namespace app\modules\budget\repositories;

use app\modules\budget\models\ManagementPeriod;
use app\modules\budget\repositories\exceptions\EntityDeleteException;
use app\modules\budget\repositories\exceptions\EntitySaveErrorException;

/**
 * Class BudgetDepartmentRepository
 * @package app\modules\budget\repositories
 */
class ManagementPeriodRepository
{
    /**
     * @var ManagementPeriod $managementPeriod
     */
    private $managementPeriod;

    /**
     * ManagementPeriodRepository constructor.
     * @param ManagementPeriod $managementPeriod
     */
    public function __construct(ManagementPeriod $managementPeriod)
    {
        $this->managementPeriod = $managementPeriod;
    }

    /**
     * @param ManagementPeriod $model
     * @return bool
     * @throws EntitySaveErrorException
     */
    public function save(ManagementPeriod $model): bool
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
     * @param ManagementPeriod $model
     * @return bool
     * @throws EntityDeleteException
     * @throws EntitySaveErrorException
     */
    public function delete(ManagementPeriod $model): bool
    {
        $model->is_deleted = true;

        if (!$this->save($model)) {
            throw new EntityDeleteException('Management period model dont soft deleted');
        }

        return true;
    }

    /**
     * @return ManagementPeriod[]
     */
    public function getAll(): array
    {
        return $this->managementPeriod::find()->all();
    }

    /**
     * @return array
     */
    public function getNames(): array
    {
        $data = $this->managementPeriod::find()->select(['name'])->asArray()->all();

        $result = [];
        foreach ($data as $item) {
            $result[] = $item['name'];
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getKeys(): array
    {
        $data = $this->managementPeriod::find()->select(['key'])->asArray()->all();
        $result = [];
        foreach ($data as $item) {
            $result[] = $item['key'];
        }

        return $result;
    }
}
