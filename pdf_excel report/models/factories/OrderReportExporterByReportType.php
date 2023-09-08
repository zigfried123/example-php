<?php

namespace app\modules\reports\models\orders\factories;

use common\services\OrderStatusService;
use frontend\modules\reports\models\orders\services\OrderReportExporterService;

class OrderReportExporterByReportType
{
    const REPORT_TYPE_CLIENT = 'client';
    const REPORT_TYPE_COMPANY = 'company';
    const REPORT_TYPE_STATISTIC = 'statistic';

    /**
     * @param $reportType
     *
     * @return mixed
     */
    public function initialize($reportType)
    {
        $classname = 'app\\modules\\reports\\models\\orders\\exporters\\byReport\\Order' . ucfirst($reportType) . 'ReportExporter';

        return new $classname();
    }


    /**
     * @return array
     */
    protected function getColumns()
    {

        return [
            [
                'attribute' => 'order_number',
                'label'     => t('order', 'Order number'),
                'value'     => function ($model) {
                    return $model->order_number;
                },
            ],
            [
                'attribute' => 'order_time',
                'label'     => t('order', 'Order time'),
                'value'     => function ($model) {
                    return app()->formatter->asDateTime($model->create_time + $model->getOrderOffset(), 'short');
                },
            ],
            [
                'attribute' => 'pre_order_time',
                'label'     => t('order', 'Pre order time'),
                'value'       => function ($model) {
                    return OrderReportExporterService::getPreOrderTime($model);
                },
            ],
            [
                'attribute' => 'order_end_time',
                'label'     => t('order', 'Order end time'),
                'value'     => function ($model) {

                    return OrderReportExporterService::getCompletedOrderTime($model);

                },
            ],
            [
                'attribute' => 'device',
                'label'     => t('order', 'Device'),
                'value'     => function ($model) {
                    return $model->getDeviceName();
                },
            ],
            [
                'attribute' => 'status',
                'label'     => t('order', 'Status'),
                'value'     => function ($model) {
                    return OrderStatusService::translate($model->status_id, $model->position_id);
                },
            ],
            [
                'attribute' => 'address',
                'label'     => t('order', 'Address'),
                'value'     => function ($model) {
                    return $model->getAddress();
                },
            ],
            [
                'attribute' => 'worker',
                'label'     => t('employee', 'Worker'),
                'value'     => function ($model) {
                    return $model->worker->fullName ?: '';
                },

            ],
            [
                'attribute' => 'car',
                'label'     => t('car', 'Car'),
                'value'     => function ($model) {
                    return !empty($model->car) ? $model->car->name . ' ' . $model->car->gos_number : '';
                },
            ],
            [
                'attribute' => 'review',
                'label'     => t('order', 'Review'),
                'value'     => function ($model) {
                    return $model->clientReview->text ?: '';
                },
            ],
            [
                'attribute' => 'rating',
                'label'     => t('client', 'Rating'),
                'value'     => function ($model) {
                    return $model->clientReview->rating ?: '';
                },
            ],
            [
                'attribute' => 'wait_time',
                'label'     => t('order', 'Time waiting') . '(' . t('app', 'min.') . ')',
                'value'     => function ($model) {
                    return (string)$model->getWaitTime();
                },
            ],
            [
                'attribute' => 'summary_time',
                'label'     => t('order', 'Summary time') . '(' . t('app', 'min.') . ')',
                'value'     => function ($model) {
                    return (string)$model->getSummaryTime();
                },
            ],
            [
                'attribute' => 'summary_distance',
                'label'     => t('order', 'Summary distance') . '(' . t('app', 'km') . ')',
                'value'     => function ($model) {
                    return (string)$model->getSummaryDistance();
                },
            ],
            [
                'attribute' => 'summary_cost',
                'label'     => t('order', 'Cost'),
                'value'     => function ($model) {
                    return (string)$model->getSummaryCost();
                },
            ],

        ];


    }

}