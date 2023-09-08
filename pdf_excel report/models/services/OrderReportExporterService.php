<?php

namespace frontend\modules\reports\models\orders\services;

use app\modules\order\models\OrderChangeData;
use app\modules\order\models\OrderStatus;

class OrderReportExporterService
{

    private static function isPreOrder($orderId)
    {
        return OrderChangeData::find()
            ->select('order_id')
            ->where([
                'order_id'     => $orderId,
                'change_field' => 'status_id',
                'change_val'   => [OrderStatus::STATUS_PRE, OrderStatus::STATUS_PRE_NOPARKING],
            ])
            ->one();
    }

    public static function getPreOrderTime($model)
    {

        $orderId = $model->order_id;

        $isPreOrder = self::isPreOrder($orderId);

        $time = false;

        if (isset($isPreOrder)) {
            $time = $model->find()->select('order_time')->where([
                'order_id' => $orderId,
            ])
                ->scalar();
        }

        if (!$time) {
            return '';
        }

        return app()->formatter->asDateTime($time + $model->getOrderOffset(), 'short');

    }

    public static function isCompletedOrder($statusId)
    {
       return in_array($statusId, OrderStatus::getCompletedStatusId());
    }

    public static function getCompletedOrderTime($model)
    {
        if(self::isCompletedOrder($model->status_id)) {
            return app()->formatter->asDateTime($model->status_time + $model->getOrderOffset(), 'short');
        }

        return '';
    }

}