<?php
namespace app\modules\budget\resources;

/**
 * Класс для синхронизации справочника отделов, контролирующих бюджет
 * @package app\modules\budget\resources
 */
class ResourceBudgetDepartment
{
    /** @var int */
    private $key;

    /** @var string $code */
    private $code;

    /** @var string $name */
    private $name;

    /**
     * ResourceBudgetDepartment constructor.
     * @param int $key
     * @param string $code
     * @param string $name
     */
    public function __construct(int $key, string $code, string $name)
    {
        $this->key = $key;
        $this->code = $code;
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getKey(): int
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Возвращает true, если все поля идентичны
     *
     * @param ResourceBudgetDepartment $item
     * @return bool
     */
    public function equals(ResourceBudgetDepartment $item): bool
    {
        return $item->code === $this->code && $item->name === $this->name;
    }
}
