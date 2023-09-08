<?php

/* @var $model \app\modules\client\models\OrderClientSearch */
/* @var $this yii\web\View */

use yii\helpers\Html;
use app\modules\order\models\Order;
use yii\helpers\Url;

$icon   = Html::tag('i', null, ['class' => Order::getDeviceIconCssClass($model->device)]);
$device = Html::tag('span', $icon . '<b>' . Html::encode($model->getDeviceName()) . '</b>');

$userCreated = $model->checkActive();

$company = t('client', 'From client') . '<br/>';

if (isset($model->client)) {
    $company .= Html::a($model->client->getFullName() !== '' ? Html::encode($model->client->getFullName()) : $model->client->clientPhones[0]->value,
        ['/client/base/update', 'id' => $model->client_id]);
}
$device = Html::tag('span',
    Html::tag('span',
        Html::tag('i', null, ['class' => Order::getDeviceIconCssClass($model->device)]) . ' '
        . Html::tag('b', Html::encode($model->getDeviceName())) . '<br>' . Html::tag('a',
            getShortName(Html::encode($userCreated['last_name']), Html::encode($userCreated['name'])),
            ['href' => Url::to(['/tenant/user/update', 'id' => $model->user_create])])
    ) . $company,
    ['class' => 'cli_app']);

$address = [];
foreach ($model->address as $key => $item) {
    $a = [];
    if (!empty($item['city'])) {
        $a[] = $item['city'];
    }
    if (!empty($item['street'])) {
        $a[] = $item['street'];
    }
    if (!empty($item['house'])) {
        $a[] = $item['house'];
    }
    $a         = implode(', ', $a);
    $address[] = Html::tag('div',
        Html::tag('span', Html::encode($key), ['class' => 'od_d'])
        . Html::tag('div',
            Html::tag('span', $a . Html::tag('i', Html::encode($item['parking'])))
        ),
        ['class' => 'order_direction']);
}
$address = implode('', $address);

echo $device . $address;
