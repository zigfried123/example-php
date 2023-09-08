<?php

namespace app\modules\budget\jobs;

use app\modules\budget\models\Project;
use app\modules\budget\providers\contracts\ProjectProviderInterface;
use app\modules\budget\resources\ResourceProject;
use app\modules\budget\services\ProjectService;
use Throwable;
use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\RetryableJobInterface;

class ProjectSyncJob extends BaseObject implements JobInterface, RetryableJobInterface
{
    const TIME_REPEAT = 600;

    /** @var ProjectProviderInterface */
    private $projectProvider;

    /** @var ProjectService */
    private $projectService;

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->projectProvider = Yii::$container->get(ProjectProviderInterface::class);
        $this->projectService = Yii::$container->get(ProjectService::class);
    }

    /**
     * @inheritDoc
     */
    public function execute($queue)
    {
        $resources = $this->projectProvider->getAll();

        $forAdd = $this->projectService->extractNew($resources);
        $forUpdate = $this->projectService->extractUpdate($resources);
        $forDelete = $this->projectService->extractDelete($resources);

        foreach ($forAdd as $item) {
            $this->projectService->add($item);
        }

        foreach ($forUpdate as $code => $item) {
            /** @var Project $model */
            $model = $item['model'];

            /** @var ResourceProject $resource */
            $resource = $item['resource'];

            $model->name = $resource->getName();

            $this->projectService->update($model);
        }

        foreach ($forDelete as $item) {
            $this->projectService->delete($item);
        }
    }

    /**
     * @inheritDoc
     */
    public function getTtr()
    {
        return self::TIME_REPEAT;
    }

    /**
     * @inheritDoc
     */
    public function canRetry($attempt, $error)
    {
        return ($error instanceof Throwable);
    }
}
