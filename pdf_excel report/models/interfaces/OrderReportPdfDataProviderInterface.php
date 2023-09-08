<?php
/**
 * Created by PhpStorm.
 * User: zigfried123
 * Date: 14.02.2018
 * Time: 10:22
 */

namespace app\modules\reports\models\orders\interfaces;


interface OrderReportPdfDataProviderInterface extends OrderReportExcelDataProviderInterface
{
    public function getContent($searchModel);
}