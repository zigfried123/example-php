<?php

namespace app\modules\budget\clients\dto\handbook;

class Field
{
    /** @var int */
    private $id;

    /** @var string */
    private $value;

    /** @var string */
    private $text;

    public function __construct(int $id, string $value, string $text)
    {
        $this->id = $id;
        $this->value = $value;
        $this->text = $text;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }
}
