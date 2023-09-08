<?php
/**
 * Created by PhpStorm.
 * User: zigfried123
 * Date: 13.02.2018
 * Time: 9:54
 */

namespace app\modules\reports\models\orders\data_providers;

use app\modules\reports\models\orders\exporters\byExport\OrderExcelReportExporter;
use app\modules\reports\models\orders\factories\OrderReportExporterByReportType;
use app\modules\reports\models\orders\interfaces\OrderReportExcelDataProviderInterface;
use yii\db\Exception;

class OrderReportExcelDataProvider implements OrderReportExcelDataProviderInterface
{
    private $rows;
    private $headers;

    public function getData($dataInit)
    {

        if ($dataInit['query']->count() == 0) {
            throw new Exception('Data is empty');
        }

        $obj = (new OrderReportExporterByReportType())->initialize($dataInit['reportType']);


        $headers = [];

        $rows = [];

        foreach ($obj->getColumns() as $column) {
            $headers[] = $column['label'];
            foreach ($dataInit['query']->all() as $key => $model) {
                $rows[$key][$column['label']] = call_user_func($column['value'], $model);
            }

        }

        $this->rows    = $rows;
        $this->headers = $headers;

        return $this;
    }

    public function setExporter()
    {

        return new OrderExcelReportExporter($this->headers, $this->rows,
                'report.xlsx');

    }

}