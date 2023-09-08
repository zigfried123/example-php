<?php

namespace app\modules\order\models\interfaces;

/**
 * Created by PhpStorm.
 * User: zigfried123
 * Date: 02.03.2018
 * Time: 12:51
 */
interface PriceCalculationByMixedInterface
{
    public static function getDistanceUnit();

    public static function getTimeUnit();
}