<?php
/**
 * Created by PhpStorm.
 * User: zigfried123
 * Date: 07.03.2018
 * Time: 11:24
 */

namespace app\modules\order\models;

use app\modules\order\models\abstractClasses\PriceCalculationAbstract;
use app\modules\order\models\factories\PriceCalculation;
use common\helpers\DateTimeHelper;
use yii\helpers\Html;

/**
 * Helps to extract need values from data array
 * Class PriceCalculationHelper
 * @package app\modules\order\models
 */
class PriceCalculationService
{
    private $model;
    private $strategy;

    public function __construct(PriceCalculationStrategy $strategy)
    {
        if (!($this->strategy instanceof $strategy)) {
            $this->strategy = $strategy;
        }
    }

    public function setModel(PriceCalculation $model)
    {
        $this->model = $model;
    }

    public function getInstance(PriceCalculationAbstract $obj)
    {
        if (!($this->model->instance instanceof $obj)) {
            $this->model->instance = $obj;
        }

        return $this->model->instance;
    }


    /**
     * Returns accrual object if it is checked
     *
     * @param $byCity
     * @param $accrual
     *
     * @return bool
     */
    public function getByCityInfoAccrual($byCity, $accrual)
    {
        $accruals = $accrual === 'DISTANCE' ? [$accrual, 'MIXED', 'INTERVAL'] : [$accrual, 'MIXED'];

        return !$this->strategy->isFixed() && $this->strategy->checkAccrualOnCondition($byCity,
            $accruals) ? $this->model->getInstance($accrual) : false;
    }


    public function getPlantingIncludeByAttr($attr)
    {

        $func = "getPlantingInclude$attr";

        switch ($this->model->info['start_point_location']) {
            case 'in':
                return is_callable([
                    $this->model->inCityByAccrual,
                    $func,
                ]) ? $this->model->inCityByAccrual->$func() : false;
            case 'out':
                return is_callable([
                    $this->model->outCityByAccrual,
                    $func,
                ]) ? $this->model->outCityByAccrual->$func() : false;
            default:
                return false;
        }

    }

    public function getOutCityInfoDistanceByAttr()
    {
        return $this->strategy->checkOnObject($this->getByCityInfoAccrual('outCity', 'DISTANCE'));
    }

    public function getInCityInfoDistanceByAttr()
    {
        return $this->strategy->checkOnObject($this->getByCityInfoAccrual('inCity', 'DISTANCE'));
    }

    public function getOutCityInfoTimeByAttr()
    {
        return $this->strategy->checkOnObject($this->getByCityInfoAccrual('outCity', 'TIME'));
    }

    public function getInCityInfoTimeByAttr()
    {
        return $this->strategy->checkOnObject($this->getByCityInfoAccrual('inCity', 'TIME'));
    }

    public function getPlantingInclude()
    {
        return [
            'label' => $this->getPlantingIncludeByAttr('Label'),
            'value' => $this->getPlantingIncludeByAttr('Value'),
        ];
    }

    public function getPlantingPrice()
    {
        $plantingPrice = null;

        if (!$this->strategy->isEmptyPlantingPrice()) {
            $plantingPrice = !$this->strategy->isFixed()
                ? $this->model->info['planting_price']
                : $this->model->info['summary_cost'] - $this->model->info['before_time_wait_cost'];
        }

        $val = !empty($plantingPrice) ? Html::encode($plantingPrice) : 0;

        $byCityByAccrual = $this->strategy->checkPlantingPrice();

        return [
            'label' => $byCityByAccrual->getPlantingPriceLabel(),
            'value' => $val . PHP_EOL . $this->model->currencySymbol,
        ];
    }


    public function getDistanceForPlant()
    {

        if ($this->strategy->checkDistanceForPlant()) {
            return [
                'label' => $this->model->outCityByAccrual->getDistanceForPlantLabel(),
                'value' => $this->model->outCityByAccrual->getDistanceForPlantValue(),
            ];
        }

        if (!$this->strategy->checkDistanceForPlantOnAccrual()) {
            return false;
        }

        return [
            'label' => t('order', 'Distance for submission car:'),
            'value' => 0 . PHP_EOL . $this->model->currencySymbol,
        ];

    }

    public function getBeforeTimeWait()
    {
        if ($this->strategy->checkBeforeTimeWait()) {
            return [
                'label' => t('order',
                    'Time waiting before landing in the car: {time} ({price} {currency}/{price-unit})',
                    [
                        'time'       => Html::encode(DateTimeHelper::secondsToStr(round($this->model->info['before_time_wait'] * 60))),
                        'price'      => Html::encode($this->strategy->isInStartPointLocation()
                            ? $this->model->info['city_wait_price'] : $this->model->info['out_wait_price']),
                        'currency'   => $this->model->currencySymbol,
                        'price-unit' => $this->model->units['minute'],
                    ]),
                'value' => Html::encode($this->model->info['before_time_wait_cost']) . PHP_EOL . $this->model->currencySymbol,
            ];
        }

        return [
            'label' => t('order', 'Time waiting before landing in the car:'),
            'value' => 0 . PHP_EOL . $this->model->currencySymbol,
        ];
    }

    public function getInCityInfoDistance()
    {
        return ($obj = $this->getInCityInfoDistanceByAttr()) ? [
            'label' => $obj->getInCityInfoLabel(),
            'value' => $obj->getInCityInfoValue(),
        ] : false;
    }


    public function getInCityInfoTime()
    {
        return ($obj = $this->getInCityInfoTimeByAttr()) ? [
            'label' => $obj->getInCityInfoLabel(),
            'value' => $obj->getInCityInfoValue(),
        ] : false;
    }


    public function getOutCityInfoDistance()
    {
        return ($obj = $this->getOutCityInfoDistanceByAttr()) ? [
            'label' => $obj->getOutCityInfoLabel(),
            'value' => $obj->getOutCityInfoValue(),
        ] : false;
    }


    public function getOutCityInfoTime()
    {
        return ($obj = $this->getOutCityInfoTimeByAttr()) ? [
            'label' => $obj->getOutCityInfoLabel(),
            'value' => $obj->getOutCityInfoValue(),
        ] : false;
    }


    public function getWaitTimeOutCity()
    {
        return [
            'label' => $this->model->outCity->getTimeWaitLabel(),
            'value' => $this->model->outCity->getTimeWaitValue(),
        ];
    }

    public function getWaitTimeInCity()
    {
        return [
            'label' => $this->model->inCity->getTimeWaitLabel(),
            'value' => $this->model->inCity->getTimeWaitValue(),
        ];
    }


    public function getSummaryTime()
    {
        return [
            'label' => t('order', 'Summary time'),
            'value' => Html::encode(DateTimeHelper::secondsToStr($this->model->info['summary_time'] * 60)),
        ];
    }


    public function getSummaryDistance()
    {
        $val = isset($this->model->info['summary_distance']) ? $this->model->info['summary_distance'] : 0;

        return [
            'label' => t('order', 'Summary distance'),
            'value' => $val . PHP_EOL . t('app', 'km'),
        ];
    }

    public function getPaidByBonus()
    {
        return $this->strategy->checkPaidByBonus() ? [
            'label' => t('order', 'Paid by bonus'),
            'value' => Html::encode($this->model->info['bonus']) . Html::encode(t('currency',
                        'B') . '(' . $this->model->currencySymbol . ')'),
        ] : false;
    }

    public function getRefillBonus()
    {
        $val = !empty($this->model->info['refill_bonus']) ? Html::encode($this->model->info['refill_bonus']) : 0;

        //if($this->strategy->checkRefillBonus()) {

            return [
                'label' => t('order', 'Refilled bonus'),
                'value' => $val . PHP_EOL . Html::encode(t('currency',
                            'B') . '(' . $this->model->currencySymbol . ')'),
            ];

        //}
    }

    public function getRefillPromoBonus()
    {
        $val = !empty($this->model->info['refill_promo_bonus']) ? round(Html::encode($this->model->info['refill_promo_bonus']),
            2) : 0;

        //if($this->strategy->checkRefillPromoBonus()) {

            return [
                'label' => t('order', 'Refilled promo bonus'),
                'value' => $val . PHP_EOL . Html::encode(t('currency',
                            'B') . '(' . $this->model->currencySymbol . ')'),
            ];

        //}
    }

    public function getAdditionalOption()
    {
        return [
            'label' => t('order', 'Additional options'),
            'value' => Html::encode($this->model->info['additional_cost']) . PHP_EOL . $this->model->currencySymbol,
        ];
    }

    public function getSummaryCostWithoutDiscount()
    {
        return [
            'label' => t('order', 'Cost without discount'),
            'value' => Html::encode($this->model->info['summary_cost'] + $this->model->info['promo_discount_value']) . PHP_EOL . $this->model->currencySymbol,
        ];
    }

    public function getSummaryCostWithDiscount()
    {
        return [
            'label' => t('order', 'Cost with discount'),
            'value' => Html::encode($this->model->info['summary_cost']) . PHP_EOL . $this->model->currencySymbol,
        ];
    }

}