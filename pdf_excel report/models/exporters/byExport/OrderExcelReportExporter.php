<?php

namespace app\modules\reports\models\orders\exporters\byExport;

use app\modules\reports\models\orders\interfaces\OrderReportExporterByExportType;
use frontend\widgets\ExcelExport;

class OrderExcelReportExporter extends OrderReportExporterByExportType
{
    private $data;
    private $headers;
    private $file;

    public function __construct($headers,$data,$file)
    {
        $this->data = $data;
        $this->headers = $headers;
        $this->file = $file;
    }

    public function export()
    {

        ExcelExport::widget([
            'data'     => $this->data,
            'format'   => 'Excel5',
            'headers'  => $this->headers,
            'fileName' => $this->file,
        ]);

    }


}