<?php
/**
 * Created by PhpStorm.
 * User: zigfried123
 * Date: 28.02.2018
 * Time: 10:06
 */

namespace app\modules\order\models\factories;

use app\modules\order\models\factories\subclasses\TariffCostInCity;
use app\modules\order\models\factories\subclasses\TariffCostOutCity;

/**
 * Counts cost tariff per accrual or out city
 * Class CostPerUnitFactory
 * @package app\modules\reports\models\orders\factories
 */
abstract class TariffCost
{
    protected $accrual;
    public $info;
    public $currencySymbol;

    protected function __construct($info,$currencySymbol,$accrual)
    {
        $this->info = $info;
        $this->accrual = $accrual;
        $this->currencySymbol = $currencySymbol;
    }

    public static function getInstance(array $data)
    {
        $obj = null;

        switch(array_key_exists('accrual_city',$data)){
            case 1:
                return new TariffCostInCity($data['info'],$data['currencySymbol'],$data['accrual_city']);
            case 0:
                return new TariffCostOutCity($data['info'],$data['currencySymbol'],$data['accrual_out']);
            default:
                return false;
        }
    }

    abstract public function getTimeWaitLabel();

    abstract public function getTimeWaitValue();

    public function getAccrual()
    {
        return $this->accrual;
    }

}