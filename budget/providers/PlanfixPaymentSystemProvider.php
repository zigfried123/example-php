<?php

namespace app\modules\budget\providers;

use app\modules\budget\exceptions\PlanfixErrorException;
use app\modules\budget\providers\contracts\PaymentSystemProviderInterface;
use app\modules\budget\resources\ResourcePaymentSystem;

class PlanfixPaymentSystemProvider implements PaymentSystemProviderInterface
{
    /** @var PlanfixProvider */
    private $planfixProvider;

    /** @var int */
    private $handbookId;

    /** @var int */
    private $fieldNameId;

    public function __construct(
        PlanfixProvider $planfixProvider,
        int $handbookId,
        int $fieldNameId
    ) {
        $this->planfixProvider = $planfixProvider;

        $this->handbookId = $handbookId;
        $this->fieldNameId = $fieldNameId;
    }

    /** @inheritDoc
     * @throws PlanfixErrorException
     */
    public function getAll(): array
    {
        /** @var ResourcePaymentSystem[] $resourcePaymentSystems */
        $resourcePaymentSystems = [];

        $arrayRecords = $this->planfixProvider->getAllHandbookItems($this->handbookId);

        foreach ($arrayRecords as $record) {
            $key = $record->getKey();
            $name = null;

            foreach ($record->getFields() as $field) {
                if ($field->getId() === $this->fieldNameId) {
                    $name = $field->getValue();
                    break;
                }
            }

            $resourcePaymentSystems[] = new ResourcePaymentSystem($key, $name);
        }

        return $resourcePaymentSystems;
    }
}
