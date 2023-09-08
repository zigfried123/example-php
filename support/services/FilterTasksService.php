<?php

namespace app\modules\support\services;

class FilterTasksService
{
    /** @var PlanfixTasksService $planfixService */
    private $planfixService;
    /** @var SyncTasksService $syncService */
    private $syncService;

    public function __construct(PlanfixTasksService $planfixService, SyncTasksService $syncService)
    {
        $this->planfixService = $planfixService;
        $this->syncService = $syncService;
    }

    /**
     * Получает текстовые и числовые значения фильтров
     *
     * @return array
     * @throws \app\common\exceptions\PlanfixErrorException
     */
    public function getAllFilterIds(): array
    {
        $filters = $this->planfixService->getTaskFilters();

        $filterIds = array_column($filters['taskFilterList']['taskFilter'], 'ID');

        return $filterIds;
    }

    /**
     * Получает только числовые фильтры
     *
     * @return array
     * @throws \app\common\exceptions\PlanfixErrorException
     */
    public function getFilterNumberIds(): array
    {
        $filterIds = $this->getAllFilterIds();

        $filterNumberIds = array_filter($filterIds, function ($id) {
            return preg_match('/\d{3,}/', $id);
        });

        return $filterNumberIds;
    }

    /**
     * Получает task ids из планфикса, фильтрует задачи по ним
     *
     * @param array $tasks
     * @param int $filter
     * @return array
     * @throws \app\common\exceptions\PlanfixErrorException
     */
    public function filterTasks(array $tasks, int $filter): array
    {
        $taskIds = $this->planfixService->getTaskIdsByFilter($filter);

        $tasksByTaskId = $this->syncService->toByTaskId($tasks);

        $filteredTasks = array_intersect_key($tasksByTaskId, array_flip($taskIds));

        $tasksByCreated = $this->syncService->toByCreated($filteredTasks);

        return $tasksByCreated;
    }
}
