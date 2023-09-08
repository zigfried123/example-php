<?php

namespace app\modules\budget\providers\contracts;

use app\modules\budget\resources\ResourceCurrency;

interface CurrencyProviderInterface
{
    /**
     * @return ResourceCurrency[]
     */
    public function getAll(): array;
}
