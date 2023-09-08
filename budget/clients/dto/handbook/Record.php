<?php

namespace app\modules\budget\clients\dto\handbook;

class Record
{
    /** @var int */
    private $parentKey;

    /** @var bool */
    private $isGroup;

    /** @var int */
    private $key;

    /** @var Field[] */
    private $fields;

    public function __construct(
        int $parentKey,
        bool $isGroup,
        int $key,
        array $fields = []
    ) {
        $this->parentKey = $parentKey;
        $this->isGroup = $isGroup;
        $this->key = $key;
        $this->fields = $fields;
    }

    public function getParentKey(): int
    {
        return $this->parentKey;
    }

    public function isGroup(): bool
    {
        return $this->isGroup;
    }

    public function getKey(): int
    {
        return $this->key;
    }

    /**
     * @return Field[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }
}
