<?php
/**
 * Created by PhpStorm.
 * User: zigfried123
 * Date: 28.02.2018
 * Time: 9:18
 */

namespace app\modules\order\models\factories;

use app\modules\order\models\factories\subclasses\PriceCalculationByDistance;
use app\modules\order\models\factories\subclasses\PriceCalculationByFix;
use app\modules\order\models\factories\subclasses\PriceCalculationByMixed;
use app\modules\order\models\factories\subclasses\PriceCalculationByTime;
use app\modules\order\models\PriceCalculationHelper;
use app\modules\order\models\PriceCalculationService;

/**
 * Access to all values for view are here. Also all objects need for work are thrown here.
 * Class PriceCalculation
 * @package app\modules\order\models\factories
 */
class PriceCalculation
{
    public $info;
    public $inCity;
    public $outCity;
    public $inCityByAccrual;
    public $outCityByAccrual;
    public $units;
    public $currencySymbol;
    public $instance;
    public $isFixed;
    private $service;

    public function __construct(
        TariffCost $inCity,
        TariffCost $outCity,
        array $info,
        array $units,
        $currencySymbol,
        PriceCalculationService $service
    ) {
        $this->inCity         = $inCity;
        $this->outCity        = $outCity;
        $this->info           = $info;
        $this->units          = $units;
        $this->currencySymbol = $currencySymbol;

        if (!($this->service instanceof $service)) {
            $this->service = $service;
        }

        $this->isFixed = $info['is_fix'];
    }

    public function getInstance($accrual)
    {
        $params = ['info' => $this->info, 'units' => $this->units, 'currencySymbol' => $this->currencySymbol];

        switch ($accrual) {
            case 'DISTANCE':
            case 'INTERVAL':
                $obj = new PriceCalculationByDistance($params);
                break;
            case 'TIME':
                $obj = new PriceCalculationByTime($params);
                break;
            case 'MIXED':
                $obj = new PriceCalculationByMixed($params);
                break;
            case 'FIX':
                $obj = new PriceCalculationByFix($params);
                break;
            default:
                $obj = new PriceCalculationByDistance($params);
        }

        return $this->service->getInstance($obj);

    }

    public function setPriceInCityByAccrual()
    {
        $this->inCityByAccrual = $this->getInstance($this->inCity->getAccrual());
    }

    public function setPriceOutCityByAccrual()
    {
        $this->outCityByAccrual = $this->getInstance($this->outCity->getAccrual());
    }

    public function getHeader()
    {
        return t('order', 'Price calculation');
    }

    public function getPlantingPriceLabel()
    {
        return $this->service->getPlantingPrice()['label'];
    }

    public function getPlantingPriceValue()
    {

        return $this->service->getPlantingPrice()['value'];

    }

    public function getPlantingIncludeLabel()
    {
        return $this->service->getPlantingInclude()['label'];
    }

    public function getPlantingIncludeValue()
    {
        return $this->service->getPlantingInclude()['value'];
    }

    public function getDistanceForPlantLabel()
    {
        return $this->service->getDistanceForPlant()['label'];
    }

    public function getDistanceForPlantValue()
    {
        return $this->service->getDistanceForPlant()['value'];
    }


    public function getBeforeTimeWaitLabel()
    {
        return $this->service->getBeforeTimeWait()['label'];

    }

    public function getBeforeTimeWaitValue()
    {
        return $this->service->getBeforeTimeWait()['value'];
    }

    public function getInCityInfoDistanceLabel()
    {
        return $this->service->getInCityInfoDistance()['label'];
    }

    public function getInCityInfoDistanceValue()
    {
        return $this->service->getInCityInfoDistance()['value'];
    }

    public function getInCityInfoTimeLabel()
    {
        return $this->service->getInCityInfoTime()['label'];
    }

    public function getInCityInfoTimeValue()
    {
        return $this->service->getInCityInfoTime()['value'];
    }

    public function getOutCityInfoDistanceLabel()
    {
        return $this->service->getOutCityInfoDistance()['label'];
    }

    public function getOutCityInfoDistanceValue()
    {
        return $this->service->getOutCityInfoDistance()['value'];
    }

    public function getOutCityInfoTimeLabel()
    {
        return $this->service->getOutCityInfoTime()['label'];
    }

    public function getOutCityInfoTimeValue()
    {
        return $this->service->getOutCityInfoTime()['value'];
    }

    public function getWaitTimeOutCityLabel()
    {
        return $this->service->getWaitTimeOutCity()['label'];
    }

    public function getWaitTimeOutCityValue()
    {
        return $this->service->getWaitTimeOutCity()['value'];
    }

    public function getWaitTimeInCityLabel()
    {
        return $this->service->getWaitTimeInCity()['label'];
    }

    public function getWaitTimeInCityValue()
    {
        return $this->service->getWaitTimeInCity()['value'];
    }

    public function getSummaryTimeLabel()
    {
        return $this->service->getSummaryTime()['label'];
    }

    public function getSummaryTimeValue()
    {
        return $this->service->getSummaryTime()['value'];
    }

    public function getSummaryDistanceLabel()
    {
        return $this->service->getSummaryDistance()['label'];
    }

    public function getSummaryDistanceValue()
    {
        return $this->service->getSummaryDistance()['value'];
    }

    public function getPaidByBonusLabel()
    {
        return $this->service->getPaidByBonus()['label'];
    }

    public function getPaidByBonusValue()
    {
        return $this->service->getPaidByBonus()['value'];
    }

    public function getRefillBonusLabel()
    {
        return $this->service->getRefillBonus()['label'];
    }

    public function getRefillBonusValue()
    {
        return $this->service->getRefillBonus()['value'];
    }

    public function getRefillPromoBonusLabel()
    {
        return $this->service->getRefillPromoBonus()['label'];
    }

    public function getRefillPromoBonusValue()
    {
        return $this->service->getRefillPromoBonus()['value'];
    }

    public function getSummaryCostWithDiscountLabel()
    {
        return $this->service->getSummaryCostWithDiscount()['label'];
    }

    public function getSummaryCostWithDiscountValue()
    {
        return $this->service->getSummaryCostWithDiscount()['value'];
    }

    public function getSummaryCostWithoutDiscountLabel()
    {
        return $this->service->getSummaryCostWithoutDiscount()['label'];
    }

    public function getSummaryCostWithoutDiscountValue()
    {
        return $this->service->getSummaryCostWithoutDiscount()['value'];
    }

    public function getAdditionalOptionLabel()
    {
        return $this->service->getAdditionalOption()['label'];
    }

    public function getAdditionalOptionValue()
    {
        return $this->service->getAdditionalOption()['value'];
    }

}