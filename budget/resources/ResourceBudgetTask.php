<?php

namespace app\modules\budget\resources;

use DateTimeImmutable;

class ResourceBudgetTask
{
    /**
     * @var int - идентификатор задачи
     */
    private $id;

    /**
     * @var int - идентификатор статуса
     */
    private $status;

    /**
     * @var int - создатель задачи
     */
    private $ownerId;

    /**
     * @var DateTimeImmutable - дата создания задачи
     */
    private $created;

    /**
     * @var int - номер задачи
     */
    private $general;

    /**
     * @var int - идентификатор отдела контролирующий бюджет
     */
    private $budgetDepartmentValue;

    /**
     * @var int|null - идентификатор статьи расходов
     */
    private $costItemValue;

    /**
     * @var int - идентификатор валюты
     */
    private $currencyValue;

    /**
     * @var int|null - идентификатор платежного периода
     */
    private $managementPeriodValue;

    /**
     * @var int - идентификатор платежной системы  TODO: array
     */
    private $paymentSystemValue;

    /**
     * @var int|null - идентификатор проекта для аналитики
     */
    private $projectValue;

    /**
     * @var float - оплаченная сумма в руб.
     */
    private $amountPaid;

    /**
     * @var float - сумма в валюте
     */
    private $amountInCurrency;

    /**
     * @var float - сумма в рублях
     */
    private $amountInRub;

    public function __construct(
        int $id,
        int $status,
        int $ownerId,
        DateTimeImmutable $created,
        int $general,
        int $budgetDepartmentValue,
        int $currencyValue,
        int $paymentSystemValue,
        float $amountPaid,
        float $amountInCurrency,
        float $amountInRub,
        $projectValue= null,
        $costItemValue = null,
        $managementPeriodValue = null
    ) {
        $this->id = $id;
        $this->status = $status;
        $this->ownerId = $ownerId;
        $this->created = $created;
        $this->general = $general;
        $this->budgetDepartmentValue = $budgetDepartmentValue;
        $this->costItemValue = $costItemValue;
        $this->currencyValue = $currencyValue;
        $this->managementPeriodValue = $managementPeriodValue;
        $this->paymentSystemValue = $paymentSystemValue;
        $this->projectValue = $projectValue;
        $this->amountPaid = $amountPaid;
        $this->amountInCurrency = $amountInCurrency;
        $this->amountInRub = $amountInRub;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getOwnerId(): int
    {
        return $this->ownerId;
    }

    public function getCreated(): DateTimeImmutable
    {
        return $this->created;
    }

    public function getGeneral(): int
    {
        return $this->general;
    }

    public function getBudgetDepartmentValue(): int
    {
        return $this->budgetDepartmentValue;
    }

    /**
     * @return int|null
     */
    public function getCostItemValue()
    {
        return $this->costItemValue;
    }

    public function getCurrencyValue(): int
    {
        return $this->currencyValue;
    }

    /**
     * @return int|null
     */
    public function getManagementPeriodValue()
    {
        return $this->managementPeriodValue;
    }

    public function getPaymentSystemValue(): int
    {
        return $this->paymentSystemValue;
    }

    /**
     * @return int|null
     */
    public function getProjectValue()
    {
        return $this->projectValue;
    }

    public function getAmountPaid(): float
    {
        return $this->amountPaid;
    }

    public function getAmountInCurrency(): float
    {
        return $this->amountInCurrency;
    }

    public function getAmountInRub(): float
    {
        return $this->amountInRub;
    }
}
