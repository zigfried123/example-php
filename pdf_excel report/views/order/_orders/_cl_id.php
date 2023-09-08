<?php

/* @var $model \app\modules\client\models\OrderClientSearch */
/* @var $this yii\web\View */

use yii\helpers\Html;

$link = Html::a('№' . $model->order_number,
    ['/order/order/view' , 'order_number' => $model->order_number],
    ['class' => 'js-order-view']
);
$time = Html::tag('span', t('app', 'on') . ' ' . app()->formatter->asDate($model->order_time, 'shortDate')
    . '<br/>' . app()->formatter->asTime($model->order_time, 'short'));

echo $link . $time;
