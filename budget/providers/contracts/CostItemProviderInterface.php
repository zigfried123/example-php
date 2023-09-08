<?php

namespace app\modules\budget\providers\contracts;

use app\modules\budget\resources\ResourceCostItem;

interface CostItemProviderInterface
{
    /**
     * @return ResourceCostItem[]
     */
    public function getAll(): array;
}
