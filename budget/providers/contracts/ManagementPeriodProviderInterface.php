<?php

namespace app\modules\budget\providers\contracts;

use app\modules\budget\resources\ResourceManagementPeriod;

interface ManagementPeriodProviderInterface
{
    /**
     * @return ResourceManagementPeriod[]
     */
    public function getAll(): array;
}
