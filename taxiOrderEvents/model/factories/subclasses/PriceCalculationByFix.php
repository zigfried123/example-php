<?php
/**
 * Created by PhpStorm.
 * User: zigfried123
 * Date: 02.03.2018
 * Time: 11:24
 */

namespace app\modules\order\models\factories\subclasses;

use app\modules\order\models\abstractClasses\PriceCalculationAbstract;

class PriceCalculationByFix extends PriceCalculationAbstract
{
    public static function getUnit()
    {
        return PriceCalculationByDistance::getUnit();
    }

    public function getPlantingPriceLabel()
    {
        return t('order', 'Fixed cost');
    }


}