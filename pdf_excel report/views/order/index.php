<?php
/* @var string $pjaxId */
/* @var $this yii\web\View */
/* @var $searchModel \app\modules\client\models\OrderClientSearch */
/* @var array $cityList */
/* @var array $positionMap */
/* @var array $statistics */
/* @var array $tenantCompanyList */

$bundle = \app\modules\reports\assets\ReportsAsset::register($this);
$this->registerJsFile($bundle->baseUrl . '/orders.js');

\frontend\assets\LeafletAsset::register($this);
?>

<h1><?= t('order', 'Orders') ?></h1>

<section class="main_tabs">
    <div class="tabs_links">
        <ul>
            <li class=""><a href="#statistics" class="t01"><?= t('reports', 'Statistics') ?></a></li>
            <li class=""><a href="#order_list" data-href="<?= \yii\helpers\Url::to('/reports/order/list') ?>"
                            class="t02"><?= t('reports', 'Order list') ?></a></li>
        </ul>
    </div>
    <div class="tabs_content">
        <div id="t01" data-view="index">
            <?= $this->render('_statistic', [
                'statistics'  => $statistics,
                'searchModel' => $searchModel,
                'cityList'    => $cityList,
                'positionMap' => $positionMap,
                'tenantCompanyList' => $tenantCompanyList,
            ]) ?>
        </div>
        <div id="t02">
        </div>
    </div>
</section>