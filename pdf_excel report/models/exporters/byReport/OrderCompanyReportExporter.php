<?php
/**
 * Created by PhpStorm.
 * User: zigfried123
 * Date: 17.11.2017
 * Time: 10:40
 */

namespace app\modules\reports\models\orders\exporters\byReport;

use app\modules\reports\models\orders\factories\OrderReportExporterByReportType;

class OrderCompanyReportExporter extends OrderReportExporterByReportType
{

    public function getColumns()
    {

        $columns = parent::getColumns();

        $columns = array_merge($columns, [
                [
                    'attribute' => 'client',
                    'label'     => t('client', 'From client'),
                    'value'     => function ($model) {
                        return $model->client->getFullName();
                    },
                ],

                [
                    'attribute' => 'client_phone',
                    'label'     => t('client', 'Phone'),
                    'value'     => function ($model) {
                        return getValue($model->client->clientPhones[0]->value);
                    },
                ],

            ]
        );

        return $columns;

    }


}