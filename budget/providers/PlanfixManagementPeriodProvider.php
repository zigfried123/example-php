<?php

namespace app\modules\budget\providers;

use app\modules\budget\exceptions\PlanfixErrorException;
use app\modules\budget\providers\contracts\ManagementPeriodProviderInterface;
use app\modules\budget\resources\ResourceManagementPeriod;

class PlanfixManagementPeriodProvider implements ManagementPeriodProviderInterface
{
    /** @var PlanfixProvider */
    private $planfixProvider;

    /** @var int */
    private $handbookId;

    /** @var int */
    private $fieldNameId;

    /** @var int */
    private $fieldDateId;

    public function __construct(
        PlanfixProvider $planfixProvider,
        int $handbookId,
        int $fieldNameId,
        int $fieldDateId
    ) {
        $this->planfixProvider = $planfixProvider;

        $this->handbookId = $handbookId;
        $this->fieldNameId = $fieldNameId;
        $this->fieldDateId = $fieldDateId;
    }

    /** @inheritDoc
     * @throws PlanfixErrorException
     */
    public function getAll(): array
    {
        /** @var ResourceManagementPeriod[] $resourcePeriods */
        $resourcePeriods = [];

        $arrayRecords = $this->planfixProvider->getAllHandbookItems($this->handbookId);

        foreach ($arrayRecords as $record) {
            $key = $record->getKey();
            $name = null;
            $code = null;

            foreach ($record->getFields() as $field) {
                if ($field->getId() === $this->fieldNameId) {
                    $name = $field->getValue();
                }

                if ($field->getId() === $this->fieldDateId) {
                    $code = $field->getValue();
                }
            }

            $resourcePeriods[] = new ResourceManagementPeriod($key, $code, $name);
        }

        return $resourcePeriods;
    }
}
