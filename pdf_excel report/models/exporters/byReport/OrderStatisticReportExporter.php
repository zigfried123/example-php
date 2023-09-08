<?php
namespace app\modules\reports\models\orders\exporters\byReport;

use app\modules\reports\models\orders\factories\OrderReportExporterByReportType;
use app\modules\reports\models\orders\helpers\OrderReportExporterHelper;

class OrderStatisticReportExporter extends OrderReportExporterByReportType
{

    public function getColumns(){

        $columns = parent::getColumns();

        $columns = array_merge($columns, [
                [
                    'attribute' => 'active',
                    'label'     => t('order', 'Active'),
                    'value'     => function ($model) {
                        return OrderReportExporterHelper::getYesOrNotArray($model->client->active);
                    },
                ],
                [
                    'attribute' => 'black_list',
                    'label'     => t('order', 'Black list'),
                    'value'     => function ($model) {
                        return OrderReportExporterHelper::getYesOrNotArray($model->client->black_list);
                    },
                ],
            ]
        );

        return $columns;
    }


}