<?php

namespace app\modules\budget\providers;

use app\modules\budget\exceptions\PlanfixErrorException;
use app\modules\budget\providers\contracts\ProjectProviderInterface;
use app\modules\budget\resources\ResourceProject;

class PlanfixProjectProvider implements ProjectProviderInterface
{
    /** @var PlanfixProvider */
    private $planfixProvider;

    /** @var int */
    private $handbookId;

    /** @var int */
    private $fieldNameId;

    /** @var int */
    private $fieldCodeId;

    public function __construct(
        PlanfixProvider $planfixProvider,
        int $handbookId,
        int $fieldNameId,
        int $fieldCodeId
    ) {
        $this->planfixProvider = $planfixProvider;

        $this->handbookId = $handbookId;
        $this->fieldNameId = $fieldNameId;
        $this->fieldCodeId = $fieldCodeId;
    }

    /** @inheritDoc
     * @throws PlanfixErrorException
     */
    public function getAll(): array
    {
        /** @var ResourceProject[] $resourceProjects */
        $resourceProjects = [];

        $arrayRecords = $this->planfixProvider->getAllHandbookItems($this->handbookId);

        foreach ($arrayRecords as $record) {
            $key = $record->getKey();
            $name = null;
            $code = null;

            foreach ($record->getFields() as $field) {
                if ($field->getId() === $this->fieldNameId) {
                    $name = $field->getValue();
                }

                if ($field->getId() === $this->fieldCodeId) {
                    $code = $field->getValue();
                }
            }

            $resourceProjects[] = new ResourceProject($key, $code, $name);
        }

        return $resourceProjects;
    }
}
