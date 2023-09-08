<?php
namespace app\modules\budget\resources;

/**
 * Класс для синхронизации платежных систем
 * @package app\modules\budget\resources
 */
class ResourcePaymentSystem
{
    /** @var int */
    private $key;

    /** @var string $name */
    private $name;

    /**
     * ResourcePaymentSystem constructor.
     * @param int $key
     * @param string $name
     */
    public function __construct(int $key, string $name)
    {
        $this->key = $key;
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
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Возвращает true, если все поля идентичны
     *
     * @param ResourcePaymentSystem $item
     * @return bool
     */
    public function equals(ResourcePaymentSystem $item): bool
    {
        return $item->getName() === $this->name;
    }
}
