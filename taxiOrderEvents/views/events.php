<?php

use yii\helpers\Html;
use \app\modules\order\models\PriceCalculationHelper;
use app\modules\order\models\factories\PriceCalculation;
use app\modules\order\models\factories\TariffCostFactory;
use \app\modules\order\models\PriceCalculationStrategy;
use \frontend\modules\promocode\models\search\PromoBonusOperation;

?>
<?php if (!empty($events)): ?>
    <ul class="events_in_order">
        <? foreach ($events as $event): ?>
            <li>
                <span class="eio_time"><?= Html::encode($event['TIME']); ?></span>
                <div class="eio_content">
                    <?= $event['TEXT']; ?>
                    <? if ($event['TYPE'] === 'review'): ?>
                        <div class="eio_review">
                            <i>«<?= Html::encode($event['INFO']['REVIEW']); ?>»</i>
                            <span class="rl_stars">
                                <? for ($i = 1; $i <= 5; $i++): ?>
                                    <? if ($i <= $event['INFO']['RAITING']): ?>
                                        <i class="active"></i>
                                    <? else: ?>
                                        <i></i>
                                    <? endif ?>
                                <? endfor; ?>
                            </span>
                        </div>
                    <? elseif ($event['TYPE'] === 'order' && !empty($event['INFO'])): ?>

                    <div class="eio_details">
                        <b><?= $priceCalculation->getHeader() ?></b>
                        <ul>
                            <?php
                            function construct($val, $label)
                            {
                                return "<li>
                                                <b>$val</b>
                                                <span>$label</span>
                                            </li>";
                            }

                            ?>

                            <!--The cost of planting-->

                            <?= construct($priceCalculation->getPlantingPriceValue(),
                                $priceCalculation->getPlantingPriceLabel()); ?>

                            <!--Included in planting -->

                            <?= construct($priceCalculation->getPlantingIncludeValue(),
                                $priceCalculation->getPlantingIncludeLabel()); ?>

                            <!--Distance for plant -->

                            <?= construct($priceCalculation->getDistanceForPlantValue(),
                                $priceCalculation->getDistanceForPlantLabel()); ?>

                            <!--Before waiting time-->

                            <?= construct($priceCalculation->getBeforeTimeWaitValue(),
                                $priceCalculation->getBeforeTimeWaitLabel()); ?>

                            <!--In city information-->

                            <?= construct($priceCalculation->getInCityInfoDistanceValue(),
                                $priceCalculation->getInCityInfoDistanceLabel()); ?>

                            <?= construct($priceCalculation->getInCityInfoTimeValue(),
                                $priceCalculation->getInCityInfoTimeLabel()); ?>

                            <!--Out city information-->

                            <?= construct($priceCalculation->getOutCityInfoDistanceValue(),
                                $priceCalculation->getOutCityInfoDistanceLabel()); ?>

                            <?= construct($priceCalculation->getOutCityInfoTimeValue(),
                                $priceCalculation->getOutCityInfoTimeLabel()); ?>

                            <!--Waiting time-->

                            <?= construct($priceCalculation->getWaitTimeInCityValue(),
                                $priceCalculation->getWaitTimeInCityLabel()); ?>

                            <?= construct($priceCalculation->getWaitTimeOutCityValue(),
                                $priceCalculation->getWaitTimeOutCityLabel()); ?>

                            <!--Additional options-->

                            <?= construct($priceCalculation->getAdditionalOptionValue(),
                                $priceCalculation->getAdditionalOptionLabel()); ?>

                            <!--Summary distance-->

                            <?= construct($priceCalculation->getSummaryDistanceValue(),
                                $priceCalculation->getSummaryDistanceLabel()); ?>

                            <!--Summary time-->

                            <?= construct($priceCalculation->getSummaryTimeValue(),
                                $priceCalculation->getSummaryTimeLabel()); ?>

                            <!--Bonus information-->

                            <?= construct($priceCalculation->getPaidByBonusValue(),
                                $priceCalculation->getPaidByBonusLabel()); ?>

                            <?= construct($priceCalculation->getRefillBonusValue(),
                                $priceCalculation->getRefillBonusLabel()); ?>

                            <?= construct($priceCalculation->getRefillPromoBonusValue(),
                                $priceCalculation->getRefillPromoBonusLabel()); ?>

                            <!--Summary cost-->

                            <?= construct($priceCalculation->getSummaryCostWithoutDiscountValue(),
                                $priceCalculation->getSummaryCostWithoutDiscountLabel()); ?>

                            <?= construct($priceCalculation->getSummaryCostWithDiscountValue(),
                                $priceCalculation->getSummaryCostWithDiscountLabel()); ?>

                            <br>
                            
                            <? if (!empty($raw_cacl_data)): ?>
                                <a class="eio_raw_toggle gray_link"><?= t('order', 'Show/hide raw data') ?></a>
                                <div class="eio_details eio_raw">
                                    <b><?= t('order', 'Raw data') ?></b>
                                    <p><? dump($raw_cacl_data) ?></p>
                                    <?= $this->render('_rawCalcDataDescription') ?>
                                </div>
                            <? endif ?>
                            <? endif ?>
                    </div>
            </li>
        <? endforeach; ?>
    </ul>
<? else: ?>
    <p><?= t('order', 'No events') ?></p>
<? endif; ?>

<?php
if (\app\modules\order\services\OrderService::isOrderCompleted($order_id)) {
    echo Html::a(t('order', 'Download the receipt for the trip, *.pdf'),
        ['/reports/order/dump-orders', 'order_id' => $order_id, 'exportType' => 'pdf']);
}
?>