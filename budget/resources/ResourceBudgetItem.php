<?php

namespace app\modules\budget\resources;

/**
 * Class ResourceBudgetItem
 * @package app\modules\budget\resources
 */
class ResourceBudgetItem
{
    /**
     * @var int
     */
    private $year;
    /**
     * @var int
     */
    private $month;
    /**
     * @var string
     */
    private $costItemCode;
    /**
     * @var string
     */
    private $departmentCode;
    /**
     * @var int
     */
    private $value;

    public function __construct(int $year, int $month, string $costItemCode, string $departmentCode, int $value)
    {
        $this->year = $year;
        $this->month = $month;
        $this->costItemCode = $costItemCode;
        $this->departmentCode = $departmentCode;
        $this->value = $value;
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function getMonth(): int
    {
        return $this->month;
    }

    public function getCostItemCode(): string
    {
        return $this->costItemCode;
    }

    public function getDepartmentCode(): string
    {
        return $this->departmentCode;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function equals(ResourceBudgetItem $item): bool
    {
        return $this == $item;
    }
}
