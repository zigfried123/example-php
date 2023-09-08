<?php
/**
 * Created by PhpStorm.
 * User: zigfried123
 * Date: 13.02.2018
 * Time: 9:54
 */

namespace app\modules\reports\models\orders\data_providers;

use app\modules\order\services\OrderService;
use app\modules\reports\models\orders\exporters\byExport\OrderPdfReportExporter;
use app\modules\reports\models\orders\interfaces\OrderReportPdfDataProviderInterface;


class OrderReportPdfDataProvider implements OrderReportPdfDataProviderInterface
{

    public $content;

    public function getData($dataInit)
    {

        $content = $this->getContent($dataInit['searchModel']);

        $this->content = $content;

        return $this;

    }

    public function getContent($searchModel)
    {

        return OrderService::getModels($searchModel);
    }

    public function setExporter()
    {

        return new OrderPdfReportExporter($this->content, 'report.pdf');
    }

}