<?php
/**
 * Created by PhpStorm.
 * User: zigfried123
 * Date: 13.02.2018
 * Time: 9:57
 */

namespace app\modules\reports\models\orders\factories;

use app\modules\reports\models\orders\abstract_classes\OrderReportDataProviderAbstract;

class OrderReportDataProvider extends OrderReportDataProviderAbstract
{

    public function getData()
    {
        return $this->provider->getData($this->dataInit);
    }

}