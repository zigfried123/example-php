<?php
/**
 * Created by PhpStorm.
 * User: zigfried123
 * Date: 28.02.2018
 * Time: 10:40
 */

namespace app\modules\order\models\factories\subclasses;

use app\modules\order\models\factories\TariffCost;
use common\helpers\DateTimeHelper;
use yii\helpers\Html;

class TariffCostOutCity extends TariffCost
{

    public function getTimeWaitLabel()
    {
        return t('order', 'Downtime in outcity: {time} ({price} {currency}/{price-unit})',
            [
                'time'       => Html::encode(DateTimeHelper::secondsToStr($this->info['out_time_wait'])),
                'price'      => Html::encode($this->info['out_wait_price']),
                'currency'   => $this->currencySymbol,
                'price-unit' => PriceCalculationByTime::getUnit(),
            ]);
    }

    public function getTimeWaitValue()
    {
        $val = !empty($this->info['out_wait_cost']) ? Html::encode($this->info['out_wait_cost']) : 0;
        return $val.PHP_EOL.$this->currencySymbol;
    }

}