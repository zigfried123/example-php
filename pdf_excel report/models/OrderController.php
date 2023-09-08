<?php

namespace app\modules\reports\controllers;

use app\modules\reports\models\orders\exporters\byExport\OrderExcelReportExporter;
use app\modules\reports\models\orders\factories\OrderReportDataProvider;
use \app\modules\reports\models\orders\factories\OrderReportExporter;
use app\modules\reports\models\orders\exporters\byExport\OrderPdfReportExporter;
use app\modules\reports\models\orders\factories\OrderReportExporterByReportType;
use app\modules\reports\models\orders\OrderSearch;
use app\modules\reports\models\orders\StatisticSearch;
use app\modules\tenant\models\User;
use frontend\components\behavior\CityListBehavior;
use frontend\modules\companies\components\repositories\TenantCompanyRepository;
use frontend\modules\employee\components\position\PositionService;
use frontend\modules\reports\models\orders\services\OrderReportExporterService;
use Yii;
use yii\base\Module;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\filters\AccessControl;
use app\modules\client\models\OrderClientCompanySearch;
use app\modules\client\models\OrderClientSearch;


/**
 * Class OrderController
 * @package app\modules\reports\controllers
 * @mixin CityListBehavior
 */
class OrderController extends Controller
{
    private $positionService;

    public function behaviors()
    {
        return [
            'access'   => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['read_reports'],
                    ],
                ],
            ],
            'cityList' => [
                'class' => CityListBehavior::className(),
            ],
        ];
    }

    public function __construct($id, Module $module, PositionService $positionService, array $config = [])
    {
        $this->positionService = $positionService;
        $this->positionService->setTenantId(user()->tenant_id);

        parent::__construct($id, $module, $config);
    }

    private function getPositionMap($cityId = null)
    {
        $positions = $cityId === null
            ? $this->positionService->getAllPositions()
            : $this->positionService->getPositionsByCity($cityId);

        return empty($positions) ? [] : ArrayHelper::map($positions, 'position_id', 'name');
    }

    public function actionIndex()
    {
        $cityList          = $this->getUserCityList();
        $positionMap       = $this->getPositionMap();
        $tenantCompanyList = TenantCompanyRepository::selfCreate()->getForForm();

        $searchModel = new StatisticSearch([
            'access_city_list'     => array_keys($cityList),
            'access_position_list' => array_keys($positionMap),
        ]);

        $searchModel->load(Yii::$app->request->queryParams);

        $statistics = $searchModel->search();

        return $this->render('index',
            compact('searchModel', 'cityList', 'statistics', 'positionMap', 'tenantCompanyList'));
    }

    public function actionList()
    {
        $cityList    = $this->getUserCityList();
        $positionMap = $this->getPositionMap();

        $searchModel  = $this->getOrderSearch($cityList, $positionMap);
        $dataProvider = $searchModel->search();

        return $this->renderAjax('_grid_orders', compact('searchModel', 'dataProvider', 'cityList', 'positionMap'));
    }


    private function dumpOrders($searchModel, $exportType, $dataProvider, $reportType)
    {
        $query = $dataProvider->query->limit(3000);

        $data = (new OrderReportDataProvider(compact('searchModel', 'reportType', 'query', 'exportType')))->getData();

        if (isset($data->content)) {

            $data->content = $this->renderPartial('dump_orders', ['orders' => $data->content]);

        }

        if (is_callable([$data, 'setExporter'])) {
            $data->setExporter()->export();
        }


    }

    //set to model for dump one order
    private function setOrderId($order_id, $searchModel)
    {

        if (isset($order_id)) {
            $searchModel->order_id = $order_id;
        }

    }

    /**
     * @param $exportType
     */
    public function actionDumpOrders($exportType, $order_id = null)
    {
        $cityList    = $this->getUserCityList();
        $positionMap = $this->getPositionMap();

        $searchModel = $this->getOrderSearch($cityList, $positionMap);

        $this->setOrderId($order_id, $searchModel);

        $dataProvider = $searchModel->search();

        $this->dumpOrders($searchModel, $exportType, $dataProvider,
            OrderReportExporterByReportType::REPORT_TYPE_STATISTIC);

    }

    /**
     * @param $exportType
     * @param $company_id
     */
    public function actionDumpCompanyOrders($exportType, $company_id, $order_id = null)
    {

        $searchModel             = new OrderClientCompanySearch();
        $searchModel->type       = 'today';
        $searchModel->company_id = $company_id;

        $this->setOrderId($order_id, $searchModel);

        $dataProvider = $searchModel->search(app()->request->queryParams);

        $this->dumpOrders($searchModel, $exportType, $dataProvider,
            OrderReportExporterByReportType::REPORT_TYPE_COMPANY);

    }

    /**
     * @param $exportType
     * @param $client_id
     */
    public function actionDumpClientOrders($exportType, $client_id, $order_id = null)
    {
        $searchModel            = new OrderClientSearch();
        $searchModel->type      = 'today';
        $searchModel->client_id = $client_id;

        $this->setOrderId($order_id, $searchModel);

        $dataProvider = $searchModel->search(app()->request->queryParams);

        $this->dumpOrders($searchModel, $exportType, $dataProvider,
            OrderReportExporterByReportType::REPORT_TYPE_CLIENT);
    }

    /**
     * @param array $cityList
     * @param array $positionMap
     *
     * @return OrderSearch
     */
    private function getOrderSearch(array $cityList, array $positionMap)
    {
        $searchModel = new OrderSearch([
            'access_city_list'     => array_keys($cityList),
            'access_position_list' => array_keys($positionMap),
        ]);

        $queryParams = Yii::$app->request->queryParams;

        if (app()->user->can(User::USER_ROLE_4)) {
            $queryParams['OrderSearch']['tenant_company_id'] = user()->tenant_company_id;
        }

        $searchModel->load($queryParams);

        return $searchModel;
    }

}
