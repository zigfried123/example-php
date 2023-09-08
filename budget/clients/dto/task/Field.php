<?php

namespace app\modules\budget\clients\dto\task;

class Field
{
    /**
     * @var int - идентификатор пользовательского поля
     */
    private $id;

    /**
     * @var string - название пользовательского поля
     */
    private $name;

    /**
     * @var string|null - значение пользовательского поля
     */
    private $value;

    /**
     * @var string|null - текстовое значение пользовательского поля
     */
    private $text;

    public function __construct(int $id, string $name, $value, $text)
    {
        $this->id = $id;
        $this->name = $name;
        $this->value = $value;
        $this->text = $text;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string|null
     */
    public function getText()
    {
        return $this->text;
    }
}
