<?php

/* @var $this yii\web\View */
/* @var string $pjaxId yii\web\View */
/* @var $searchModel \app\modules\reports\models\orders\OrderSearch */
/* @var $dataProvider yii\data\ArrayDataProvider */
/* @var array $positionMap */
/* @var array $cityList */

use yii\grid\GridView;
use frontend\widgets\LinkPager;
use yii\widgets\Pjax;
use frontend\widgets\filter\Filter;
use yii\helpers\Url;
use app\modules\tenant\models\User;

$pjaxId = 'pjax_order_reports';
$widgetId = 'order_report_form';

$filter = Filter::begin([
    'id'       => $widgetId,
    'pjaxId'   => $pjaxId,
    'model'    => $searchModel,
    'onSubmit' => true,
    'onChange' => false,
]);
echo $filter->dropDownList('city_id', $cityList);
echo $filter->checkboxList('position_id', $positionMap, ['label' => t('reports', 'All professions')]);

if (!app()->user->can(User::USER_ROLE_4)) {
    echo $filter->checkboxList('tenantCompanyIds', $searchModel->getTenantCompanies(), ['label' => t('tenant_company', 'Companies')]);
}

echo $filter->checkboxList('payment', $searchModel->getPaymentMap(), ['label' => t('reports', 'All payments')]);
echo $filter->checkboxList('device', $searchModel->getDeviceMap(), ['label' => t('reports', 'All sources')]);
echo $filter->checkboxList('status_id', $searchModel->getStatusMap(), ['label' => t('reports', 'All statuses')]);
echo $filter->checkboxList('class_id', $searchModel->getCarClassMap(), ['label' => t('reports', 'All car classes')]);
echo $filter->radioList('type',
    [
        'today'     => [
            'label' => t('reports', 'Today'),
        ],
        'yesterday' => [
            'label' => t('reports', 'Yesterday'),
        ],
        'month'     => [
            'label' => app()->formatter->asDate(time(), 'LLLL'),
        ],
        'period'    => [
            'label'   => t('reports', 'Period'),
            'content' => $filter->datePeriod(['first_date', 'second_date'], [
                date("d.m.Y", mktime(0, 0, 0, date("m"), date("d") - 2, date("Y"))),
                date("d.m.Y", mktime(0, 0, 0, date("m"), date("d"), date("Y"))),
            ], ['class' => 'cof_date']),
        ],
    ], [], ['class' => 'filter_item_2', 'style' => 'width:100%; margin-bottom: 15px;']);

echo $filter->input('order_number', ['placeholder' => t('order', 'Order number')]);
echo $filter->input('callsign', ['placeholder' => t('employee', 'Callsign')]);

echo $filter->button(t('reports','Show'), [], ['style' => 'width:auto']);

echo $filter->export([
    [
        'label' => t('client', 'List of orders in Excel'),
        'url'   => ['/reports/order/dump-orders/excel'],
    ],
    [
        'label' => t('client', 'Receipts about trips in PDF'),
        'url'   => ['/reports/order/dump-orders/pdf'],
    ],
], ['style' => 'width:auto'], 'Download');

Filter::end();

$this->registerJs('select_init(); filter.radioBlockInit("#' . $widgetId . '");');

Pjax::begin(['id' => $pjaxId]);

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'layout'       => "{items}\n{pager}",
    'tableOptions' => [
        'class' => 'people_table',
    ],
    'pager'        => [
        'class'         => LinkPager::className(),
        'prevPageLabel' => t('app', 'Prev'),
        'nextPageLabel' => t('app', 'Next'),
    ],
    'columns'      => [
        [
            'label'          => '',
            'attribute'      => 'order_number',
            'content'        => function ($model) {
                return $this->render('_orders/_cl_id', ['model' => $model]);
            },
            'contentOptions' => ['class' => 'cl_id'],
            'headerOptions'  => ['style' => 'width:10%'],
        ],
        [
            'label'          => t('order', 'Address'),
            'attribute'      => 'info',
            'content'        => function ($model) {
                return $this->render('_orders/_cl_info', ['model' => $model]);
            },
            'contentOptions' => ['class' => 'cl_info'],
//            'headerOptions' => ['style' => 'width:25%'],
        ],
        [
            'label'          => t('order', 'Status'),
            'attribute'      => 'staus_id',
            'content'        => function ($model) {
                return $this->render('_orders/_cl_status', ['model' => $model]);
            },
            'contentOptions' => ['class' => 'cli_details'],
            'headerOptions'  => ['style' => 'width:18%'],
        ],
        [
            'label'          => t('app', 'Worker'),
            'attribute'      => 'worker',
            'content'        => function ($model) {
                return $this->render('_orders/_cl_worker', ['model' => $model]);
            },
            'contentOptions' => ['class' => 'cli_details'],
            'headerOptions'  => ['style' => 'width:17%'],
        ],
        [
            'label'          => '',
            'attribute'      => 'review',
            'content'        => function ($model) {
                return $this->render('_orders/_cl_review', ['model' => $model]);
            },
            'contentOptions' => ['class' => 'cli_review'],
            'headerOptions'  => ['style' => 'width:20%'],
        ],
        [
            'label'          => '',
            'attribute'      => 'predv_price',
            'content'        => function ($model) {
                return $this->render('_orders/_cl_costs', ['model' => $model]);
            },
            'contentOptions' => ['style' => 'text-align: right'],
            'headerOptions'  => ['style' => 'width:8%'],
        ],
    ],
]);

Pjax::end();