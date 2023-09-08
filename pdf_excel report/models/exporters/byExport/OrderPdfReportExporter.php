<?php

namespace app\modules\reports\models\orders\exporters\byExport;

use app\modules\reports\models\orders\interfaces\OrderReportExporterByExportType;
use kartik\mpdf\Pdf;

class OrderPdfReportExporter extends OrderReportExporterByExportType
{
    private $content;
    private $file;

    public function __construct($content,$file)
    {
        $this->content = $content;
        $this->file = $file;
    }

    public function export()
    {

        // setup kartik\mpdf\Pdf component
        $pdf = new Pdf([
            // set to use core fonts only
            'mode'        => Pdf::MODE_UTF8,
            // A4 paper format
            'format'      => Pdf::FORMAT_A4,
            // portrait orientation
            'orientation' => Pdf::ORIENT_PORTRAIT,
            // stream to browser inline
            'destination' => Pdf::DEST_DOWNLOAD,
            'filename'    => $this->file,
            // your html content input
            'content'     => $this->content,
            // format content from your own css file if needed or use the
            // enhanced bootstrap css built by Krajee for mPDF formatting
            'cssFile'     => '@vendor/kartik-v/yii2-mpdf/assets/kv-mpdf-bootstrap.min.css',
            // any css to be embedded if required
            'cssInline'   => '.kv-heading-1{font-size:18px}',
            // set mPDF properties on the fly
            'options'     => ['title' => 'Krajee Report Title'],
            // call mPDF methods on the fly

        ]);


        // return the pdf output as per the destination setting
        return $pdf->render();

    }

}
