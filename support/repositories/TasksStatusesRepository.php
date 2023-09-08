<?php

namespace app\modules\support\repositories;

use app\modules\tickets\helpers\ApiPlanfix;
use DateTime;
use Exception;
use RuntimeException;

/**
 * Class TasksStatusesRepository
 * @package app\modules\support\repositories
 */
class TasksStatusesRepository
{
    /**
     * @var int  ид фильтра "Шаблон"
     */
    const TEMPLATE_TYPE = 51;
    /**
     * @var int ИД шаблона ТПиП
     */
    const TEMPLATE_ID = 10987280;
    /**
     *
     */
    const STATUS_TYPE = 10;
    /**
     * @var int
     */
    const STATUS_ID = 3;
    /**
     * @var int
     */
    const DATE_CREATED_TYPE = 12;
    /**
     * @var int Дата последнего изменения или комментария
     */
    const DATE_MODIFIED_TYPE = 79;
    /**
     * @var ApiPlanfix
     */
    private $apiPlanfix;

    /**
     * TasksStatusesRepository constructor.
     * @param ApiPlanfix $apiPlanfix
     */
    public function __construct(ApiPlanfix $apiPlanfix)
    {
        $this->apiPlanfix = $apiPlanfix;
    }


    public function getList(DateTime $modified, int $page, int $pageSize): array
    {
        $response = $this->apiPlanfix->sendRequest(
            ApiPlanfix::METHOD_TASK_LIST,
            [
                'filters' => [
                    'filter' => [
                        [
                            'type' => self::TEMPLATE_TYPE,
                            'operator' => 'equal',
                            'value' => self::TEMPLATE_ID,
                        ],
                        [
                            'type' => self::DATE_MODIFIED_TYPE,
                            'operator' => 'gt',
                            'value' => [
                                'datetype' => 'anotherdate',
                                'datefrom' => $modified->format('d-m-Y'),
                            ],
                        ],
                    ],
                ],
                'pageCurrent' => $page,
                'pageSize' => $pageSize,
                'sort' => 'NUMBER_ASC',
            ]
        );

        if (!$this->apiPlanfix->isResponseOk($response)) {
            throw new RuntimeException('API response: ' . json_encode($response));
        }

        return $response;
    }
}
