<?php

namespace app\modules\budget\providers\contracts;

use app\modules\budget\resources\ResourcePaymentSystem;

interface PaymentSystemProviderInterface
{
    /**
     * @return ResourcePaymentSystem[]
     */
    public function getAll(): array;
}
