<?php

namespace app\modules\budget\providers\contracts;

use app\modules\budget\resources\ResourceProject;

interface ProjectProviderInterface
{
    /**
     * @return ResourceProject[]
     */
    public function getAll(): array;
}
