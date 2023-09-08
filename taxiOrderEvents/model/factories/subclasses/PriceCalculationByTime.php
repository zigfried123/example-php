<?php
/**
 * Created by PhpStorm.
 * User: zigfried123
 * Date: 28.02.2018
 * Time: 10:58
 */

namespace app\modules\order\models\factories\subclasses;

use app\modules\order\models\abstractClasses\PriceCalculationAbstract;
use app\modules\order\models\interfaces\PriceCalculationByTimeInterface;
use common\helpers\DateTimeHelper;
use yii\helpers\Html;

class PriceCalculationByTime extends PriceCalculationAbstract implements PriceCalculationByTimeInterface
{
    public static function getUnit()
    {
        return t('app', 'min.');
    }

    public function getPlantingIncludeLabel()
    {
        return t('order', 'Price includes {value} {unit}', [
            'value' => Html::encode($this->info['planting_include']),
            'unit'  => self::getUnit(),
        ]);
    }

    public function getPlantingIncludeValue()
    {
        return 0 . PHP_EOL . $this->currencySymbol;
    }

    public function getInCityInfoLabel()
    {

        $price = !empty($this->info['city_next_km_price_time']) ? Html::encode($this->info['city_next_km_price_time']) : 0;

        return t('order', 'City: {time} ({price} {currency}/{price-unit})', [
            'time'       => Html::encode(DateTimeHelper::secondsToStr($this->info['city_time'] * 60)),
            'price'      => $price,
            'currency'   => $this->currencySymbol,
            'price-unit' => self::getUnit(),
        ]);

    }

    /**
     * added the condition here so as wrong logic of writing TIME. Value should be wrote in city_cost_time
     * @return string
     */
    public function getInCityInfoValue()
    {
        $val = !empty($this->info['city_cost_time']) ? Html::encode($this->info['city_cost_time']) : 0;

        if(empty($val)) {
            $val = !empty($this->info['city_cost']) ? Html::encode($this->info['city_cost']) : 0;
        }
        
        return $val . PHP_EOL . $this->currencySymbol;
    }

    public function getOutCityInfoLabel()
    {
        return t('order', 'Outcity: {time} ({price} {currency}/{price-unit})',
            [
                'time'       => Html::encode(DateTimeHelper::secondsToStr($this->info['out_city_time'] * 60)),
                'price'      => !empty($this->info['out_next_km_price_time']) ? Html::encode($this->info['out_next_km_price_time']) : 0,
                'currency'   => $this->currencySymbol,
                'price-unit' => self::getUnit(),
            ]);

    }

    /**
     * added the condition here so as wrong logic of writing TIME. Value should be wrote in out_city_cost_time
     * @return string
     */
    public function getOutCityInfoValue()
    {
        $val = !empty($this->info['out_city_cost_time']) && $this->info['out_city_cost_time']>0 ? Html::encode($this->info['out_city_cost_time']) : 0;

        if(empty($val)) {
            $val = !empty($this->info['out_city_cost']) && $this->info['out_city_cost']>0 ? Html::encode($this->info['out_city_cost']) : 0;
        }

        return $val . PHP_EOL . $this->currencySymbol;
    }

}