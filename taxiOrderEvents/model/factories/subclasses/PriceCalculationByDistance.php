<?php
/**
 * Created by PhpStorm.
 * User: zigfried123
 * Date: 28.02.2018
 * Time: 10:58
 */

namespace app\modules\order\models\factories\subclasses;

use app\modules\order\models\abstractClasses\PriceCalculationAbstract;
use app\modules\order\models\interfaces\PriceCalculationByDistanceInterface;
use yii\helpers\Html;

class PriceCalculationByDistance extends PriceCalculationAbstract implements PriceCalculationByDistanceInterface
{

    public static function getUnit()
    {
        return t('app', 'km');
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

        $price = !empty($this->info['city_next_km_price']) ? Html::encode($this->info['city_next_km_price']) : 0;

        if($this->info['accrual_city'] === 'INTERVAL') {

            return t('order', 'City: {value} {unit}',
                [
                    'value'      => Html::encode($this->info['city_distance']),
                    'unit'       => self::getUnit(),
                ]);

        }

        return t('order', 'City: {value} {unit} ({price} {currency}/{price-unit})',
            [
                'value'      => Html::encode($this->info['city_distance']),
                'unit'       => self::getUnit(),
                'price'      => $price,
                'currency'   => $this->currencySymbol,
                'price-unit' => self::getUnit(),
            ]);

    }

    public function getInCityInfoValue()
    {
        $val = isset($this->info['city_cost']) ? Html::encode($this->info['city_cost']) : 0;

        return $val . PHP_EOL . $this->currencySymbol;
    }

    public function getOutCityInfoLabel()
    {
        $price = !empty($this->info['out_next_km_price']) ? Html::encode($this->info['out_next_km_price']) : 0;

        if($this->info['accrual_out'] === 'INTERVAL') {

            return t('order', 'Outcity: {value} {unit}',
                [
                    'value'      => Html::encode($this->info['out_city_distance']),
                    'unit'       => self::getUnit(),
                ]);

        }

        return t('order', 'Outcity: {value} {unit} ({price} {currency}/{price-unit})',
            [
                'value'      => Html::encode($this->info['out_city_distance']),
                'unit'       => self::getUnit(),
                'price'      => $price,
                'currency'   => $this->currencySymbol,
                'price-unit' => self::getUnit(),
            ]);

    }

    public function getOutCityInfoValue()
    {
        $val = isset($this->info['out_city_cost']) && $this->info['out_city_cost']>0 ? Html::encode($this->info['out_city_cost']) : 0;

        return $val . PHP_EOL . $this->currencySymbol;
    }


}