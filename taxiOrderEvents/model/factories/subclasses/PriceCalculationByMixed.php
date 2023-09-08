<?php
/**
 * Created by PhpStorm.
 * User: zigfried123
 * Date: 28.02.2018
 * Time: 14:56
 */

namespace app\modules\order\models\factories\subclasses;

use app\modules\order\models\abstractClasses\PriceCalculationAbstract;
use app\modules\order\models\interfaces\PriceCalculationByMixedInterface;
use yii\helpers\Html;

class PriceCalculationByMixed extends PriceCalculationAbstract implements PriceCalculationByMixedInterface
{
    public static function getDistanceUnit()
    {
        return PriceCalculationByDistance::getUnit();
    }

    public static function getTimeUnit()
    {
        return PriceCalculationByTime::getUnit();
    }

    public function getPlantingIncludeLabel()
    {

        return t('order', 'Price includes {distanceValue} {distanceUnit} and {timeValue} {timeUnit}', [
            'timeValue' => Html::encode($this->info['planting_include_time']),
            'distanceValue' => Html::encode($this->info['planting_include']),
            'timeUnit'  => self::getTimeUnit(),
            'distanceUnit'  => self::getDistanceUnit(),
        ]);
    }

    public function getPlantingIncludeValue()
    {
        return 0 . PHP_EOL . $this->currencySymbol;
    }

}