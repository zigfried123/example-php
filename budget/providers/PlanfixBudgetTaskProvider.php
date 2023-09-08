<?php

namespace app\modules\budget\providers;

use app\modules\budget\clients\PlanfixClient;
use app\modules\budget\providers\contracts\BudgetTaskProviderInterface;
use app\modules\budget\resources\ResourceBudgetTask;
use DateTimeImmutable;

class PlanfixBudgetTaskProvider implements BudgetTaskProviderInterface
{
    /** @var PlanfixClient */
    private $planfixClient;

    /** @var int */
    private $fieldPaymentSystemId;

    /** @var int */
    private $fieldDepartmentId;

    /** @var int */
    private $fieldManagementPeriodId;

    /** @var int */
    private $fieldProjectId;

    /** @var int */
    private $fieldItemCostId;

    /** @var int */
    private $fieldCurrencyId;

    /** @var int */
    private $fieldAmountPaidId;

    /** @var int */
    private $fieldAmountInCurrencyId;

    /** @var int */
    private $fieldAmountInRubId;

    public function __construct(
        PlanfixClient $planfixClient,
        int $fieldPaymentSystemId,
        int $fieldDepartmentId,
        int $fieldManagementPeriodId,
        int $fieldProjectId,
        int $fieldItemCostId,
        int $fieldCurrencyId,
        int $fieldAmountPaidId,
        int $fieldAmountInCurrencyId,
        int $fieldAmountInRubId
    ) {
        $this->planfixClient = $planfixClient;
        $this->fieldPaymentSystemId = $fieldPaymentSystemId;
        $this->fieldDepartmentId = $fieldDepartmentId;
        $this->fieldManagementPeriodId = $fieldManagementPeriodId;
        $this->fieldProjectId = $fieldProjectId;
        $this->fieldItemCostId = $fieldItemCostId;
        $this->fieldCurrencyId = $fieldCurrencyId;
        $this->fieldAmountPaidId = $fieldAmountPaidId;
        $this->fieldAmountInCurrencyId = $fieldAmountInCurrencyId;
        $this->fieldAmountInRubId = $fieldAmountInRubId;
    }

    public function get(int $id): ResourceBudgetTask
    {
        $task = $this->planfixClient->getBudgetTask($id);

        $paymentSystem = null;
        $department = null;
        $managementPeriod = null;
        $project = null;
        $itemCost = null;
        $currency = null;
        $amountPaid = null;
        $amountInCurrency = null;
        $amountInRub = null;

        foreach ($task->getCustomFields() as $customField) {
            switch ($customField->getId()) {
                case $this->fieldPaymentSystemId:
                    $paymentSystem = $customField->getValue();
                    break;
                case $this->fieldDepartmentId:
                    $department = $customField->getValue();
                    break;
                case $this->fieldManagementPeriodId:
                    $managementPeriod = $customField->getValue();
                    break;
                case $this->fieldProjectId:
                    $project = $customField->getValue();
                    break;
                case $this->fieldItemCostId:
                    $itemCost = $customField->getValue();
                    break;
                case $this->fieldCurrencyId:
                    $currency = $customField->getValue();
                    break;
                case $this->fieldAmountPaidId:
                    $amountPaid = $this->strToFloat($customField->getValue());
                    break;
                case $this->fieldAmountInCurrencyId:
                    $amountInCurrency = $this->strToFloat($customField->getValue());
                    break;
                case $this->fieldAmountInRubId:
                    $amountInRub = $this->strToFloat($customField->getValue());
                    break;
            }
        }

        return new ResourceBudgetTask(
            $task->getId(),
            $task->getStatus(),
            $task->getOwnerId(),
            DateTimeImmutable::createFromFormat('d-m-Y H:i', $task->getBeginDateTime()),
            $task->getGeneral(),
            $department,
            $currency,
            $paymentSystem,
            $amountPaid,
            $amountInCurrency,
            $amountInRub,
            $project,
            $itemCost,
            $managementPeriod
        );
    }

    private function strToFloat(string $str): float
    {
        $result = str_replace(',', '.', $str);
        $result = filter_var($result, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        return $result;
    }
}
