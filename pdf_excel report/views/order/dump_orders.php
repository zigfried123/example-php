<?php

/* @var $orders app\modules\reports\models\orders\OrderSearch */

use \yii\helpers\Html;
use \app\modules\order\services\OrderService;
use \app\modules\reports\helpers\OrderReportExporterHelper;

?>
<div style="border-width: 1px 1px 0 1px; border-style: solid; border-color: #000; font-family: Arial, Helvetica, sans-serif;">
    <?php
    $freighter = '';
    if (array_key_exists(0, $orders)) {
        $freighter = OrderReportExporterHelper::getFreighter($orders[0]->tenant);
    }
    foreach ($orders as $order):
        ?>
        <table style="width: 100%;">
            <tbody>
            <tr>
                <td colspan="2" style="padding: 10px 10px 5px 10px;">
                    <p>
                        Фрахтовщик: <?= $freighter; ?>
                    </p>
                </td>
            </tr>
            <tr>
                <td style="padding: 10px;">
                    <p style="font-size: 18px; font-weight: bold;">
                        <span>КВИТАНЦИЯ </span>
                        <span>Серия АА № <?= Html::encode($order->order_number); ?></span>
                    </p>
                    <p>
                        <span>на оплату пользования легковым такси</span>
                    </p>
                    <br>
                    <p>
                        <span>Фрахтователь: <?php
                            $charterer = Html::encode(!empty($order->client) ? $order->client->getFullName() : '') . ' ' . Html::encode(!empty($order->client->clientPhones) ? $order->client->clientPhones[0]->value : '');
                            if (trim($charterer) !== '0') {
                                echo $charterer;
                            }
                            unset($charterer);
                            ?>
                        </span>
                    </p>
                    <p>
                        <span>Место подачи: <?= OrderReportExporterHelper::getAddressStart($order->address); ?></span>
                    </p>
                    <p>
                        <span>Место назначения: <?= count($order->address) > 1 ? OrderReportExporterHelper::getAddressEnd($order->address) : '' ?></span>
                    </p>
                    <p>
                        <span>Время ожидания: <?= $order->getWaitTime() . ' мин' ?></span>
                    </p>
                    <p>
                        <span>Форма оплаты: <?= Yii::t('commission', Html::encode($order->payment)); ?></span>
                    </p>
                </td>
                <td style="vertical-align: top;">
                    <table cellpadding="0" cellspacing="0"
                           style="border-width: 1px 0 1px 1px; border-style: solid; border-color: #000;">
                        <tbody>
                        <tr>
                            <td style="border-width: 0 1px 1px 0; border-style: solid; border-color: #000; padding: 3px 10px;">
                                <p>
                                    <span>Дата подачи</span>
                                </p>
                            </td>
                            <td style="border-width: 0 0 1px 0; border-style: solid; border-color: #000; padding: 3px 10px;">
                                <p>
                                    <span>
                                        <?php
                                        if ($time = OrderService::getOrderArriveTime($order)) {
                                            echo date('d.m.Y в H:i', $time);
                                        }
                                        unset($time);
                                        ?>
                                    </span>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td style="border-width: 0 1px 1px 0; border-style: solid; border-color: #000; padding: 3px 10px;">
                                <p>
                                    <span>Дата отправления </span>
                                </p>
                            </td>
                            <td style="border-width: 0 0 1px 0; border-style: solid; border-color: #000; padding: 3px 10px;">
                                <p>
                                    <span>
                                        <?php
                                        if ($time = OrderService::getOrderDepartureTime($order)) {
                                            echo date('d.m.Y в H:i', $time);
                                        }
                                        unset($time);
                                        ?>
                                    </span>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td style="border-width: 0 1px 1px 0; border-style: solid; border-color: #000; padding: 3px 10px;">
                                <p>
                                    <span>Дата завершения </span>
                                </p>
                            </td>
                            <td style="border-width: 0 0 1px 0; border-style: solid; border-color: #000; padding: 3px 10px;">
                                <p>
                                    <span><?= date('d.m.Y в H:i',
                                            $order->status_time + $order->getOrderOffset()); ?></span>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td style="border-width: 0 1px 1px 0; border-style: solid; border-color: #000; padding: 3px 10px;">
                                <p>
                                    <span>№ машины </span>
                                </p>
                            </td>
                            <td style="border-width: 0 0 1px 0; border-style: solid; border-color: #000; padding: 3px 10px;">
                                <p>
                                    <span><?= Html::encode($order->car->gos_number) ?></span>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="padding: 3px 10px;">
                                <span>Тариф ожидания <?= Html::encode(isset($order->detailCost->city_wait_price) ? $order->detailCost->city_wait_price . ' руб/мин' : null); ?>
                                    <br>
                                    Тариф на проезд <?php
                                    if (isset($order->detailCost->city_next_cost_unit, $order->detailCost->city_next_km_price)) {
                                        $cost = null;
                                        switch ($order->detailCost->city_next_cost_unit) {
                                            case '1_minute':
                                                $cost = 'руб/мин';
                                                break;
                                            case '30_minute':
                                                $cost = 'руб/30мин';
                                                break;
                                            case '1_hour':
                                                $cost = 'руб/час';
                                                break;
                                            case '1_km':
                                                $cost = 'руб/км';
                                                break;
                                            case 'ytn':
                                                $cost = 'руб/км';
                                                break;
                                        }

                                        $text=null;

                                        switch ($order->detailCost->accrual_city) {
                                            case 'DISTANCE':
                                                $text = Html::encode($order->detailCost->city_next_km_price) . ' ' . $cost;
                                                break;
                                            case 'TIME':
                                                $text = Html::encode($order->detailCost->city_next_km_price) . ' ' . $cost;
                                                break;
                                            case 'MIXED':
                                                $text = Html::encode($order->detailCost->city_next_km_price) . ' руб/км' . ' ' . Html::encode($order->detailCost->city_next_km_price_time) . ' ' . $cost;
                                                break;
                                            case 'INTERVAL':
                                                $array = unserialize($order->detailCost->city_next_km_price);
                                                if (is_array($array)) {
                                                    $text = 'от '.current($array)['price'] . ' ' . $cost;
                                                }
                                                break;
                                            case 'FIX':
                                                $text = 'тарифная сетка';
                                                break;
                                        }

                                        echo $text;


                                        unset($cost,$array,$text);
                                    }
                                    ?>
                                </span>
                                <p>
                                    <span>Показания таксометра <?= Html::encode($order->getSummaryDistance()) . ' км'; ?></span>
                                </p>
                            </td>

                        </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            </tbody>
        </table>
        <p style="padding: 10px 10px 5px 10px;">
            <span>Дата выдачи квитанции на оплату пользования легковым такси <?= date('d.m.Y в H:i',
                    $order->status_time + $order->getOrderOffset()); ?></span>
        </p>
        <p style="padding: 0 10px 5px 10px;">
            <?php $arr = list($rub, $cop) = explode('.', $order->detailCost->summary_cost) ?>
            <span>Стоимость пользования легковым такси
                <?php if (!empty($order->detailCost->summary_cost)) { ?>
                    <?= Html::encode($rub . ' руб.') . ' ' . Html::encode(isset($cop) ? $cop . ' коп.' : 0 . ' коп.'); ?>
                <?php } ?>
            </span>
            <?php unset($arr); ?>
        </p>
        <table>
            <tbody>
            <tr>
                <td style="padding: 0 10px 10px 10px;">
                    <p>
                        <span>Лицо, уполномоченное на проведение расчетов<br><?= !empty($order->worker) ? $order->worker->getFullName() : '' ?></span>
                    </p>
                </td>
                <td style="padding: 0 10px 10px 10px;">
                    <p>
                        <span>Пассажир<br><?= !empty($order->client) ? $order->client->getFullName() : ''; ?></span>
                    </p>
                </td>
            </tr>
            </tbody>
        </table>
        <div style="border-bottom: 1px solid #000;"></div>

        <?php
    endforeach;
    ?>
</div>


