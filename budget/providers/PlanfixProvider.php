<?php

namespace app\modules\budget\providers;

use app\modules\budget\clients\dto\handbook\Record;
use app\modules\budget\clients\PlanfixClient;
use app\modules\budget\exceptions\PlanfixErrorException;

class PlanfixProvider
{
    /** @var PlanfixClient */
    private $planfixClient;

    public function __construct(PlanfixClient $planfixClient)
    {
        $this->planfixClient = $planfixClient;
    }

    /**
     * @param int $handbookId
     * @param int $parentKey
     * @return Record[]
     * @throws PlanfixErrorException
     */
    public function getAllHandbookItems(int $handbookId, int $parentKey = 0): array
    {
        $records = $this->planfixClient->getHandbookRecords($handbookId, $parentKey);

        $resultArray = [];

        foreach ($records as $record) {
            if ($record->isGroup()) {
                $resultArray = array_merge(
                    $resultArray,
                    $this->getAllHandbookItems($handbookId, $record->getKey())
                );
                continue;
            }

            $resultArray[] = $record;
        }

        return $resultArray;
    }
}
