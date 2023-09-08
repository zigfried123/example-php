<?php

/* @var $model \app\modules\client\models\OrderClientSearch */
/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\helpers\StringHelper;

if (!empty($model->clientReview)) {
    $rating = [];
    for ($i = 0; $i < (int)$model->clientReview->rating; $i++) {
        $rating[] = Html::tag('i', '', ['class' => 'active']);
    }
    for ($i = (int)$model->clientReview->rating; $i < 5; $i++) {
        $rating[] = Html::tag('i', '');
    }
    $ratingStr = Html::tag('span', implode(PHP_EOL, $rating), ['class' => 'clir_stars']);
    $text = Html::encode($model->clientReview->text);
    $textStr = '';
    if (!empty($text)) {
        $previewTextSize = 20;
        if (mb_strlen($text) > $previewTextSize) {
            $textStr = Html::tag('i', '«' . StringHelper::truncate($text, $previewTextSize) . '»',
                    ['class' => 'js-preview'])
                . Html::tag('i', '«' . $text . '»', ['style' => 'display:none;', 'class' => 'js-full'])
                . Html::a(t('client', 'Fully'), null, ['class' => 'js-spoiler review_spoler']);
        } else {
            $textStr = Html::tag('i', '«' . $text . '»');
        }
    }
    echo $ratingStr . Html::tag('div', $textStr, ['class' => 'clir_content']);
}