<?php

namespace app\modules\reports\models\orders\interfaces;

abstract class OrderReportExporterByExportType
{
    const EXPORT_TYPE_EXCEL = 'excel';
    const EXPORT_TYPE_PDF = 'pdf';

    abstract public function export();

}