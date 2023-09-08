<?php
namespace app\modules\budget\resources;

/**
 * Класс для синхронизации управленческих периодов
 * @package app\modules\budget\resources
 */
class ResourceManagementPeriod
{
    /** @var int */
    private $key;

    /** @var string $date */
    private $date;

    /** @var string $name */
    private $name;

    /**
     * @param string $date
     * @param string $name
     */
    public function __construct(int $key, string $date, string $name)
    {
        $this->key = $key;
        $this->date = $date;
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
    public function getDate(): string
    {
        return $this->date;
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
     * @param ResourceManagementPeriod $item
     * @return bool
     */
    public function equals(ResourceManagementPeriod $item): bool
    {
        return $item->getDate() === $this->date && $item->getName() === $this->name;
    }
}
