<?php
/**
 * Created by PhpStorm.
 * User: zigfried123
 * Date: 14.02.2018
 * Time: 10:19
 */

namespace app\modules\reports\models\orders\interfaces;


interface OrderReportExcelDataProviderInterface
{

    public function getData($dataInit);

    public function setExporter();

}