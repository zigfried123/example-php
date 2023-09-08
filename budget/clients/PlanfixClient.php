<?php

namespace app\modules\budget\clients;

use app\modules\budget\clients\dto\handbook\Record;
use app\modules\budget\clients\dto\task\Task;
use app\modules\budget\clients\converters\ToHandbook;
use app\modules\budget\clients\converters\ToTask;
use app\modules\budget\exceptions\PlanfixErrorException;
use app\modules\tickets\helpers\ApiPlanfix;

class PlanfixClient
{
    /** @var ApiPlanfix */
    private $apiPlanfix;

    /** @var ToHandbook */
    private $handbookConverter;

    /** @var ToTask */
    private $taskConverter;

    public function __construct(
        ApiPlanfix $apiPlanfix,
        ToHandbook $handbookConverter,
        ToTask $taskConverter
    ) {
        $this->apiPlanfix = $apiPlanfix;
        $this->handbookConverter = $handbookConverter;
        $this->taskConverter = $taskConverter;
    }

    public function getBudgetTask(int $id): Task
    {
        $body = [
            'task' => [
                'id' => $id
            ]
        ];

        $response = $this->apiPlanfix->sendRequest(ApiPlanfix::METHOD_TASK_GET, $body);

        if (!$this->apiPlanfix->isResponseOk($response)) {
            throw new PlanfixErrorException(json_encode($body), $response['code']);
        }

        return $this->taskConverter->convert($response['task']);
    }


    /**
     * @param int $handbookId
     * @param int $parentKey
     * @param int $page
     * @param int $pageSize
     * @return Record[]
     * @throws PlanfixErrorException
     */
    public function getHandbookRecords(
        int $handbookId,
        int $parentKey = 0,
        int $page = 1,
        int $pageSize = 100
    ): array {

        $body = [
            'handbook' => [
                'id' => $handbookId,
            ],
            'parentKey' => $parentKey,
            'pageCurrent' => $page,
            'pageSize' => $pageSize,
        ];

        $response = $this->apiPlanfix->sendRequest(
            ApiPlanfix::METHOD_HANDBOOK_GET_RECORDS,
            $body
        );

        if (!$this->apiPlanfix->isResponseOk($response)) {
            throw new PlanfixErrorException(json_encode($body), $response['code']);
        }

        return $this->handbookConverter->convert($response['records']);
    }
}