<?php

/* @var $model \app\modules\client\models\OrderClientSearch */
/* @var $this yii\web\View */

use common\services\OrderStatusService;
use yii\helpers\Html;

$status = Html::tag('b',
    Html::encode(OrderStatusService::translate($model->status_id, $model->position_id)),
    ['class' => 'green']);

echo $status;