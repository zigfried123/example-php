<?php
namespace app\modules\budget\resources;

/**
 * Класс для синхронизации справочника валют
 * @package app\modules\budget\resources
 */
class ResourceCurrency
{
    /** @var int */
    private $key;

    /** @var string $code */
    private $code;

    /** @var string $name */
    private $name;

    /**
     * ResourceCurrency constructor.
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
     * @param ResourceCurrency $item
     * @return bool
     */
    public function equals(ResourceCurrency $item): bool
    {
        return $item->getCode() === $this->code && $item->getName() === $this->name;
    }
}
