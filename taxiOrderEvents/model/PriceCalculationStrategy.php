<?php
/**
 * Created by PhpStorm.
 * User: zigfried123
 * Date: 14.03.2018
 * Time: 12:10
 */

namespace app\modules\order\models;

use app\modules\order\models\factories\PriceCalculation;

/**
 * All conditions must be here.
 * Class PriceCalculationStrategy
 * @package app\modules\order\models
 */
class PriceCalculationStrategy
{
    private $model;

    public function setModel(PriceCalculation $model)
    {
        $this->model = $model;
    }

    /**
     * Returns object if accrual is included in array of available accruals
     * @param       $byCity
     * @param array $accruals
     *
     * @return bool
     */
    public function checkAccrualOnCondition($byCity, array $accruals)
    {
        return in_array($this->model->$byCity->getAccrual(), $accruals, true);
    }

    public function isFixed()
    {
        return $this->model->isFixed;
    }

    public function checkPlantingInclude()
    {
        return !$this->model->isFixed && isset($this->model->info['planting_include']);
    }

    private function isEmptyCityTimeWait()
    {
        return empty($this->model->info['city_time_wait']);
    }

    public function checkWaitTime()
    {
        return !$this->isFixed() && !$this->isEmptyCityTimeWait();
    }

    public function checkOnObject($obj)
    {
        return is_object($obj) ? $obj : 0;
    }

    private function isEmptyStartPointLocation()
    {
        return empty($this->model->info['start_point_location']);
    }

    private function isEmptySupplyPrice()
    {
        return empty($this->model->info['supply_price']);
    }

    private function isEmptyDistanceForPlant()
    {
        return empty($this->model->info['distance_for_plant']);
    }

    public function checkDistanceForPlantOnAccrual()
    {
        switch($this->model->outCity->getAccrual()){
            case 'FIX':
            case 'TIME':
                return 0;
            default:
                return 1;
        }
    }

    public function checkDistanceForPlant()
    {
        return (!$this->isFixed() && !$this->isEmptySupplyPrice() && !$this->isEmptyStartPointLocation() && !$this->isEmptyDistanceForPlant());
    }

    private function isEmptyAdditionalOption()
    {
        return empty($this->model->info['additional_cost']);
    }

    public function checkAdditionalOption()
    {
        return !$this->isFixed() && !$this->isEmptyAdditionalOption();
    }

    public function checkPaidByBonus()
    {
        return !empty($this->model->info['bonus']);
    }

    public function checkRefillBonus()
    {
        return !empty($this->model->info['refill_bonus']);
    }

    public function checkRefillPromoBonus()
    {
        return !empty($this->model->info['refill_promo_bonus']);
    }

    public function checkBeforeTimeWait()
    {
        return !empty($this->model->info['before_time_wait']);
    }

    public function isEmptyPlantingPrice()
    {
        return empty($this->model->info['planting_price']);
    }

    public function isInStartPointLocation()
    {
        return $this->model->info['start_point_location'] === 'in';
    }

    public function checkPlantingPrice()
    {
        return $this->isFixed() ? $this->model->getInstance('FIX') : $this->model->inCityByAccrual;
    }

}