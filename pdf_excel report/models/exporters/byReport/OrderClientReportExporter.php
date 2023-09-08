<?php
/**
 * Created by PhpStorm.
 * User: zigfried123
 * Date: 17.11.2017
 * Time: 10:41
 */

namespace app\modules\reports\models\orders\exporters\byReport;

use app\modules\reports\models\orders\factories\OrderReportExporterByReportType;
use app\modules\reports\models\orders\helpers\OrderReportExporterHelper;

class OrderClientReportExporter extends OrderReportExporterByReportType
{
    public function getColumns()
    {

        $columns = parent::getColumns();

        $columns = array_merge($columns, [
                [
                    'attribute' => 'From organization',
                    'label'     => t('client', 'From organization'),
                    'value'     => function ($model) {
                        return !empty($model->company) ? $model->company->name : '';
                    },
                ],
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