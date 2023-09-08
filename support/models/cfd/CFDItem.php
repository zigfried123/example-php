<?php

namespace app\modules\support\models\cfd;

use app\modules\support\models\status\Type;

class CFDItem
{
    /** @var int */
    private $amountSeconds;

    /** @var Type */
    private $type;

    /**
     * CFDItem constructor.
     * @param int $amountSeconds
     * @param Type $type
     */
    public function __construct(int $amountSeconds, Type $type)
    {
        $this->amountSeconds = $amountSeconds;
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getAmountSeconds(): int
    {
        return $this->amountSeconds;
    }

    /**
     * @return Type
     */
    public function getType(): Type
    {
        return $this->type;
    }
}