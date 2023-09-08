<?php
/**
 * Created by PhpStorm.
 * User: zigfried123
 * Date: 28.02.2018
 * Time: 10:38
 */

namespace app\modules\order\models\factories\subclasses;

use app\modules\order\models\factories\TariffCost;
use common\helpers\DateTimeHelper;
use yii\helpers\Html;

class TariffCostInCity extends TariffCost
{

    public function getTimeWaitLabel()
    {
        return t('order', 'Downtime in city: {time} ({price} {currency}/{price-unit})',
            [
                'time'       => Html::encode(DateTimeHelper::secondsToStr($this->info['city_time_wait'])),
                'price'      => Html::encode($this->info['city_wait_price']),
                'currency'   => $this->currencySymbol,
                'price-unit' => PriceCalculationByTime::getUnit(),
            ]);
    }

    public function getTimeWaitValue()
    {
        $val = !empty($this->info['city_wait_cost']) ? Html::encode($this->info['city_wait_cost']) : 0;
        return $val.PHP_EOL.$this->currencySymbol;
    }


}