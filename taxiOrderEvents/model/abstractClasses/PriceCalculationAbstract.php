<?php

namespace app\modules\order\models\abstractClasses;


use yii\helpers\Html;

abstract class PriceCalculationAbstract
{
    protected $info;
    protected $units;
    protected $currencySymbol;

    public function __construct(array $data)
    {
        $this->info           = $data['info'];
        $this->units          = $data['units'];
        $this->currencySymbol = $data['currencySymbol'];
    }

    public function getDistanceForPlantLabel()
    {
        return t('order',
            'Distance for submission car: {distance} km. ({price} {currency}/km.)',
            [
                'distance' => Html::encode($this->info['distance_for_plant']),
                'price'    => $this->info['start_point_location'] === 'in' ? 0 : Html::encode($this->info['supply_price']),
                'currency' => $this->currencySymbol,
            ]);
    }

    public function getDistanceForPlantValue()
    {
        return Html::encode($this->info['distance_for_plant_cost']) . PHP_EOL . $this->currencySymbol;
    }

    public function getPlantingPriceLabel()
    {
        return t('order', 'Submission car');
    }





}