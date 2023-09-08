<?php

/* @var $model \app\modules\client\models\OrderClientSearch */
/* @var $this yii\web\View */

echo $model->getSummaryCost() . '<br>'
    . $model->getSummaryDistance() . ' ' . t('app', 'km') . '<br>'
    . $model->getSummaryTime() . ' ' . t('app', 'min.');