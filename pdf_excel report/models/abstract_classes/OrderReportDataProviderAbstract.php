<?php
/**
 * Created by PhpStorm.
 * User: zigfried123
 * Date: 13.02.2018
 * Time: 10:05
 */

namespace app\modules\reports\models\orders\abstract_classes;

use app\modules\reports\models\orders\data_providers\OrderReportExcelDataProvider;
use app\modules\reports\models\orders\data_providers\OrderReportPdfDataProvider;
use app\modules\reports\models\orders\interfaces\OrderReportExporterByExportType;

abstract class OrderReportDataProviderAbstract
{
    public function __construct($dataInit)
    {
        $this->dataInit = $dataInit;

        if ($this->isExcel()) {

            $this->provider = new OrderReportExcelDataProvider($dataInit);


        } elseif ($this->isPdf()) {
            $this->provider = new OrderReportPdfDataProvider();

        }
    }

    private function isExcel()
    {
        return $this->dataInit['exportType'] === OrderReportExporterByExportType::EXPORT_TYPE_EXCEL;

    }

    private function isPdf()
    {
        return $this->dataInit['exportType'] === OrderReportExporterByExportType::EXPORT_TYPE_PDF;

    }


    abstract public function getData();


}