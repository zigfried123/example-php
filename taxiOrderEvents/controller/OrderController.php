<?php

namespace app\modules\order\controllers;

use app\modules\order\models\factories\PriceCalculation;
use app\modules\order\models\factories\TariffCost;
use app\modules\order\models\OrderSearchByString;
use app\modules\order\models\forms\FilterMapForm;
use app\modules\client\models\Client;
use app\modules\order\models\PriceCalculationService;
use app\modules\order\models\PriceCalculationStrategy;
use app\modules\tenant\models\User;
use frontend\components\serviceEngine\ServiceEngine;
use app\modules\order\exceptions\InvalidAttributeValueException;
use app\modules\order\models\Call;
use app\modules\parking\models\Parking;
use bonusSystem\BonusSystem;
use common\modules\car\models\CarOption;
use common\modules\order\models\OrderTrackService;
use common\modules\tenant\models\DefaultSettings;
use common\services\OrderStatusService;
use frontend\modules\employee\components\worker\WorkerService;
use frontend\modules\employee\components\worker\WorkerShiftService;
use frontend\modules\order\components\ActionManager;
use frontend\modules\order\components\datacentr\DataForFilterMap;
use frontend\modules\order\components\OrderRequestResult;
use frontend\modules\order\components\OrderService;
use frontend\modules\promocode\models\search\PromoBonusOperation;
use Yii;
use yii\base\ErrorException;
use yii\db\Exception;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use common\modules\city\models\City;
use app\modules\order\models\Order;
use app\modules\order\models\OrderStatus;
use app\modules\order\models\OrderChangeData;
use app\modules\order\models\OrderDetailCost;
use app\modules\client\models\ClientSearch;
use app\modules\client\models\ClientReview;
use app\modules\client\models\ClientCompanyHasTariff;
use app\modules\tariff\models\TaxiTariff;
use common\modules\tenant\models\TenantSetting;
use frontend\modules\tenant\models\Currency;
use frontend\modules\order\exceptions\InvalidOrderTimeException;
use frontend\modules\order\exceptions\ForbiddenChangeOrderTimeException;
use frontend\modules\car\models\CarColor;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * OrderController implements the CRUD actions for Order model.
 */
class OrderController extends Controller
{
    const CREATED_UPDATE_EVENT = 10;
    const STOP_OFFER_EVENT = 11;

    protected $orderService;

    public function __construct(
        $id,
        $module,
        OrderService $orderService,
        array $config = []
    ) {
        $this->orderService = $orderService;

        parent::__construct($id, $module, $config);
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['orders'],
                    ],
                    [
                        'actions' => [
                            'create',
                            'stop-offer',
                        ],
                        'allow'   => false,
                        'roles'   => ['read_orders'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['read_orders'],
                    ],
                ],
            ],
        ];
    }

    public function beforeAction($action)
    {
        //Update active user time
        if (!\Yii::$app->user->isGuest) {
            user()->updateUserActiveTime();
        }

        return parent::beforeAction($action);
    }

    /**
     * Lists all Order models.
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\InvalidParamException
     */
    public function actionIndex()
    {

        $orderSearchByString = new OrderSearchByString();


        $city_id = get('city_id');

        $date               = get('date');
        $filterDate         = strtotime($date) ? date('d.m.Y', strtotime($date)) : null;
        $workerShiftService = $this->getWorkerShiftService();

        $formFilter    = new FilterMapForm([
            'cities' => $city_id ? $city_id : null,
        ]);
        $dataForFilter = new DataForFilterMap();

        return $this->render('@app/modules/order/views/order/index', [
            'orders'              => Order::getOrdersFromRedis(
                OrderStatus::STATUS_GROUP_0, ['city_id' => $city_id, 'date' => $filterDate]),
            'ordersCount'         => $this->orderService->getCountOrdersForTabs(['city_id' => $city_id]),
            'user_city_list'      => user()->getUserCityListWithRepublic(),
            'workers'             => $workerShiftService->getCntWorkersOnShift($city_id),
            'orderSearchByString' => $orderSearchByString,
            'formFilter'          => $formFilter,
            'dataForFilter'       => $dataForFilter,
        ]);
    }

    /**
     * @return WorkerShiftService
     * @throws \yii\base\InvalidConfigException
     */
    private function getWorkerShiftService()
    {
        return Yii::$app->getModule('employee')->get('workerShift');
    }

    public function actionGetAll($city_id = null, $date = null)
    {

        $filter = post('filter');

        $orders = OrderService::getAllOrdersByFields($filter, $date, $city_id);


        return $this->renderAjax('@app/modules/order/views/order/new', [
            'orders' => $orders,
        ]);
    }

    /**
     * Lists all Order models.
     * @return mixed
     */
    public function actionGetNew()
    {
        if (app()->user->can(User::USER_ROLE_4)) {
            return false;
        }

        $city_id = get('city_id');

        $date               = get('date');
        $filterDate         = strtotime($date) ? date('d.m.Y', strtotime($date)) : null;
        $workerShiftService = $this->getWorkerShiftService();

        return $this->renderAjax('@app/modules/order/views/order/new', [
            'orders'         => Order::getOrdersFromRedis(OrderStatus::STATUS_GROUP_0,
                ['city_id' => $city_id, 'date' => $filterDate]),
            'user_city_list' => user()->getUserCityListWithRepublic(),
            'workers'        => $workerShiftService->getCntWorkersOnShift($city_id),
        ]);
    }

    /**
     * List of all order in works.
     * @return mixed
     */
    public function actionGetWorks()
    {
        if (!Yii::$app->request->isAjax) {
            return false;
        }

        $city_id = get('city_id');

        $date       = get('date');
        $filterDate = strtotime($date) ? date('d.m.Y', strtotime($date)) : null;


        return $this->renderAjax('@app/modules/order/views/order/works', [
            'orders' => Order::getOrdersFromRedis(OrderStatus::STATUS_GROUP_8,
                ['city_id' => $city_id, 'date' => $filterDate]),
        ]);
    }

    /**
     * List of all warning order.
     * @return mixed
     */
    public function actionGetWarning()
    {
        if (!Yii::$app->request->isAjax) {
            return false;
        }
        if (app()->user->can(User::USER_ROLE_4)) {
            return false;
        }

        $date       = get('date');
        $city_id    = get('city_id');
        $filterDate = strtotime($date) ? date('d.m.Y', strtotime($date)) : null;
        $orders     = Order::getOrdersFromRedis(OrderStatus::STATUS_GROUP_7,
            ['city_id' => $city_id, 'date' => $filterDate]);
        $not_orders = ArrayHelper::getColumn($orders, 'order_id');
        $orders     = array_merge($orders,
            Order::getByStatusGroup(OrderStatus::STATUS_GROUP_7, $city_id, $filterDate, [], [], $not_orders));


        return $this->renderAjax('@app/modules/order/views/order/warning', [
            'orders' => $orders,
        ]);
    }

    /**
     * List of all pre order.
     * @return mixed
     */
    public function actionGetPreOrders()
    {
        if (!Yii::$app->request->isAjax) {
            return false;
        }


        $date       = get('date');
        $filterDate = strtotime($date) ? date('d.m.Y', strtotime($date)) : null;


        return $this->renderAjax('@app/modules/order/views/order/pre_order', [
            'orders' => Order::getOrdersFromRedis(OrderStatus::STATUS_GROUP_6,
                ['city_id' => get('city_id'), 'date' => $filterDate], SORT_ASC, 'order_time'),
        ]);
    }

    /**
     * List of all completed order.
     * @return mixed
     */
    public function actionGetCompleted()
    {
        if (!Yii::$app->request->isAjax) {
            return false;
        }

        $city_id = get('city_id');

        $cityTimeoffset = City::getTimeOffset($city_id);

        $date = get('date');
        if ($timestamp = strtotime($date)) {
            $date_timestamp = isset($cityTimeoffset) ? $timestamp + $cityTimeoffset : $timestamp;
        }
        $filterDate = isset($date_timestamp) ? date('d.m.Y', $date_timestamp) : null;
        $key        = 'or_completed_' . user()->tenant_id . '_' . $city_id . '_';


        //Today orders
        if (is_null($filterDate) || $filterDate == date('d.m.Y')) {
            $key      .= 'today';
            $statuses = implode(', ', OrderStatus::getCompletedStatusId());
            $callback = function () use ($city_id) {
                return Order::getByStatusGroup(OrderStatus::STATUS_GROUP_4, $city_id);
            };
            $orders   = Order::getTodayNoActiveOrdersFromCache($key, $statuses, $callback, 600, $city_id);
        } else {
            $orders = Order::getByStatusGroup(OrderStatus::STATUS_GROUP_4, $city_id, $filterDate);
        }


        return $this->renderAjax('@app/modules/order/views/order/completed', [
            'orders' => $orders,
        ]);
    }

    /**
     * List of all completed order.
     * @return mixed
     */
    public function actionGetRejected()
    {
        if (!Yii::$app->request->isAjax) {
            return false;
        }
        if (app()->user->can(User::USER_ROLE_4)) {
            return false;
        }

        $date           = get('date');
        $date_timestamp = strtotime($date);
        $filterDate     = $date_timestamp ? date('d.m.Y', $date_timestamp) : null;

        return $this->renderAjax('@app/modules/order/views/order/rejected', [
            'orders' => Order::getByStatusGroup(OrderStatus::STATUS_GROUP_5, get('city_id'), $filterDate),
        ]);
    }

    /**
     * Try save order
     *
     * @param Order $order
     *
     * @return boolean
     * @throws Exception
     */
    protected function trySaveOrder($order)
    {
        $retryCount = app()->params['order.save.retryCount'];
        for ($i = 0; $i < $retryCount; $i++) {
            try {
                return $order->save(false);
            } catch (Exception $ex) {
                if (strpos($ex->getMessage(), 'ui_order__tenant_id__order_number') === false) {
                    throw $ex;
                } else {
                    continue;
                }
            }
        }

        return false;
    }

    /**
     * @param Order   $order
     * @param integer $userId
     *
     * @throws ErrorException
     */
    private function sendOrderToEngine($order, $userId)
    {
        $nodeApiComponent = new ServiceEngine();

        try {
            $resultSendOrder = $nodeApiComponent->neworderAuto(
                $order->order_id, $order->tenant_id);
        } catch (\Exception $e) {
            \Yii::error("Receive error neworderAuto: orderId={$order->order_id} (Error:{$e->getMessage()})");
            $resultSendOrder = false;
        }
        if (!$resultSendOrder) {
            \Yii::$app->redis_orders_active->executeCommand('hdel', [$order->tenant_id, $order->order_id]);

            $error = null;
            try {
                $isOrderDeleted = $order->delete();
            } catch (\Exception $ex) {
                $isOrderDeleted = false;
                $error          = $ex->getMessage();
            }

            if (empty($isOrderDeleted)) {
                \Yii::error("Error delete new order from MySql, after engine was failed: orderId={$order->order_id} (Error:{$error})",
                    'order');
                \Yii::info("[order] order_id={$order->order_id},user_id={$userId} Error delete new order from MySql, after engine was failed (Error:{$error})",
                    'app-log');
            }
            \Yii::info("[order] order_id={$order->order_id},user_id={$userId} Error to save order to nodejs",
                'app-log');
            throw new ErrorException('Error to save order to service_engine');
        }
    }

    /**
     * Creates a new Order model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @param integer $city_id
     *
     * @return mixed
     * @throws \yii\base\InvalidParamException
     * @throws \yii\db\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionCreate($city_id = null)
    {
        if (!Yii::$app->request->isAjax) {
            return $this->redirect(['operator/create', 'city_id' => $city_id]);
        }

        $order            = new Order();
        $order->city_id   = $city_id;
        $order->tenant_id = user()->tenant_id;


        /* @var $actionManager ActionManager */
        $actionManager = \Yii::createObject(ActionManager::class, [app()->orderApi]);

        /** @var $orderService OrderService */
        $orderService = $this->module->orderService;

        if ($order->load(Yii::$app->request->post())) {
            app()->response->format = Response::FORMAT_JSON;

            $transaction = app()->db->beginTransaction();

            try {
                $actionManager->applyActionToOrder($order);

                if (!$this->trySaveOrder($order)) {
                    $transaction->rollBack();
                    \Yii::error('Ошибка сохранения заказа', 'order');
                    \Yii::error(implode('; ', $order->getFirstErrors()), 'order');

                    return OrderRequestResult::getResult(
                        OrderRequestResult::ACTION_ERROR,
                        t('order', 'Error saving order. Notify to administrator, please.')
                    );
                } else {
                    $call_id = post('call_id');

                    if (!empty($call_id)) {
                        $call = Call::findOne(['uniqueid' => $call_id]);
                        if (!empty($call) && empty($call->order_id)) {
                            $call->order_id = $order->order_id;
                            $call->save(false, ['order_id']);
                        }
                    }
                    $transaction->commit();

                    $userId = user()->user_id;

                    $this->sendOrderToEngine($order, $userId);

                    $orderJson = json_encode($order->getAttributes(),
                        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    \Yii::info("[order] order_id={$order->order_id},user_id={$userId} Order was created ({$orderJson})",
                        'app-log');

                    return OrderRequestResult::getResult(OrderRequestResult::ACTION_CLOSE);
                }
            } catch (InvalidOrderTimeException $exc) {
                $transaction->rollBack();

                return OrderRequestResult::getResult(
                    OrderRequestResult::ACTION_ERROR,
                    t('order', 'Order time is incorrect')
                );
            } catch (ForbiddenChangeOrderTimeException $exc) {
                $transaction->rollBack();

                return OrderRequestResult::getResult(
                    OrderRequestResult::ACTION_ERROR,
                    t('order', 'Forbidden to change the order time')
                );
            } catch (InvalidAttributeValueException $ex) {
                return OrderRequestResult::getResult(
                    OrderRequestResult::ACTION_ERROR, $ex->getMessage());
            } catch (\Exception $exc) {
                $transaction->rollBack();
                \Yii::error($exc->getMessage(), 'order');

                return OrderRequestResult::getResult(
                    OrderRequestResult::ACTION_ERROR,
                    t('order', 'Error saving order. Notify to administrator, please.')
                );
            }
        }

        $orderId = app()->session->get('copyOrder');
        if (!empty($orderId)) {
            $isCopyOrder = true;
            app()->session->remove('copyOrder');
            $order->copyFrom($orderId);
        }

        $order->status_id   = OrderStatus::STATUS_NEW;
        $order->orderAction = ActionManager::ACTION_NEW_ORDER;
        $dataForm           = $order->getFormData($city_id, $order->status_id);

        if (empty($order->order_date)) {
            $order->city_id = $city_id ? $city_id : key($dataForm['CITY_LIST']);

            $time                 = time() + $order->getOrderOffset() + $order->getPickUp();
            $order->order_now     = 1;
            $order->order_date    = date('Y-m-d', $time);
            $order->order_hours   = date('H', $time);
            $order->order_minutes = date('i', $time);
        }

        $parkingList = $order->getAddressParklingList();

        $positions   = $orderService->getPositions($order->city_id);
        $positionIds = array_keys($positions);
        if (empty($order->position_id) || !in_array($order->position_id, $positionIds, false)) {
            $order->position_id = current($positionIds);
        }
        $order->show_phone = TenantSetting::getSettingValue(user()->tenant_id,
            DefaultSettings::SETTING_SHOW_WORKER_PHONE, $order->city_id, $order->position_id);

        $tariffs   = $orderService->getTariffs($order->city_id, $order->position_id, $order->client_id, $order->payment,
            $order->company_id, true, ['operator']);
        $tariffIds = empty($tariffs) ? [] : ArrayHelper::getColumn($tariffs, 'tariff_id');
        if (empty($order->tariff_id) || !in_array($order->tariff_id, $tariffIds, false)) {
            $order->tariff_id = current($tariffIds);
        }

        $clientInfo = $orderService->getClientInformationById($order->tenant_id, $order->city_id, $order->client_id);

        $orderActions = array_map(function ($value) {
            return [$value => t('order', $value)];
        }, $actionManager->getAvailableActions(null));

        $canBonusPayment = $this->canBonusPayment($order->tenant_id, $order->client_id, $order->city_id,
            $order->tariff_id);

        return $this->renderAjax('@app/modules/order/views/order/add',
            compact('order', 'dataForm', 'parkingList', 'positions', 'tariffs', 'orderActions', 'clientInfo',
                'canBonusPayment', 'isCopyOrder'));
    }

    /**
     * Generate order hash
     *
     * @param integer $orderId
     * @param integer $statusId
     *
     * @return string
     */
    protected function generateOrderHash($orderId, $statusId)
    {
        return md5("{$orderId}_{$statusId}");
    }


    /**
     * Getting update event result
     *
     * @param int    $orderNumber
     * @param string $requestId
     *
     * @return array
     * @throws \yii\base\InvalidParamException
     * @throws \operatorApi\exceptions\GetUpdateEventResultException
     */
    public function actionGetUpdateEventResult($orderNumber, $requestId)
    {
        if (!Yii::$app->request->isAjax) {
            return false;
        }

        app()->response->format = Response::FORMAT_JSON;

        /** @var $orderService OrderService */
        $orderService = $this->module->orderService;
        $result       = $orderService->getUpdateEventResult($requestId);

        if (empty($result)) {
            $action  = OrderRequestResult::ACTION_WAIT;
            $message = $requestId;
            $url     = null;
        } else {
            $action      = $result['code'] === 100
                ? OrderRequestResult::ACTION_REDIRECT : OrderRequestResult::ACTION_ERROR;
            $message     = isset($result['info']) ? $result['info'] : null;
            $orderNumber = filter_var($orderNumber, FILTER_SANITIZE_NUMBER_INT);
            $url         = "/update?order_number={$orderNumber}";
        }

        return OrderRequestResult::getResult($action, $message, $url);
    }


    /**
     * Updates an existing Order model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param integer $order_number
     *
     * @return mixed
     * @throws \yii\base\InvalidParamException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpdate($order_number)
    {

        $orderNumber = filter_var($order_number, FILTER_SANITIZE_NUMBER_INT);

        if (!Yii::$app->request->isAjax) {
            return $this->redirect(['operator/update', 'order_number' => $orderNumber]);
        }

        /* @var $actionManager ActionManager */
        $actionManager = \Yii::createObject(ActionManager::class, [app()->orderApi]);

        /** @var $orderService OrderService */
        $orderService = $this->module->orderService;

        $order = isset($_POST['Order']) ? Order::findOne([
            'order_number' => $orderNumber,
            'tenant_id'    => user()->tenant_id,
        ]) : Order::getOrderInfo($orderNumber);

        if (in_array($order->status->status_group, [
            OrderStatus::STATUS_GROUP_COMPLETED,
            OrderStatus::STATUS_GROUP_REJECTED,
        ], false)) {
            return $this->actionView($orderNumber);
        }

        if (app()->request->isPost) {
            app()->response->format = Response::FORMAT_JSON;

            try {
                $order->load(app()->request->post());

                if ($order->orderAction === ActionManager::ACTION_COPY_ORDER) {
                    app()->session->set('copyOrder', $order->order_id);

                    return OrderRequestResult::getResult(
                        OrderRequestResult::ACTION_REDIRECT, '', '/create?city_id=' . $order->city_id);
                } elseif ($order->orderAction === ActionManager::ACTION_STOP_OFFER) {
                    $orderService->stopOffer(
                        $order->tenant_id,
                        user()->id,
                        $order->order_id,
                        $order->update_time,
                        user()->lang
                    );

                    return OrderRequestResult::getResult(
                        OrderRequestResult::ACTION_REDIRECT,
                        t('order', 'The request to stop offer to worker has been sent')
                    );
                } else {
                    $actionManager->applyActionToOrder($order);
                }

                $requestId = $orderService->createUpdateEvent($order);
                if ($requestId === null) {
                    return OrderRequestResult::getResult(
                        OrderRequestResult::ACTION_REDIRECT, '', "/update?order_number={$orderNumber}");
                } else {
                    return OrderRequestResult::getResult(
                        OrderRequestResult::ACTION_WAIT, $requestId);
                }

            } catch (\Exception $ex) {
                \Yii::error($ex, 'order');

                return OrderRequestResult::getResult(
                    OrderRequestResult::ACTION_ERROR,
                    t('order', 'Error saving order. Notify to administrator, please.')
                );
            }
        }

        $order->hash              = $this->generateOrderHash($order->order_id, $order->status_id);
        $order->order_now         = 0;
        $order->order_date        = date('Y-m-d', $order->order_time);
        $order->order_hours       = date('H', $order->order_time);
        $order->order_minutes     = date('i', $order->order_time);
        $order->order_seconds     = date('s', $order->order_time);
        $order->additional_option = ArrayHelper::map($order->options, 'option_id', 'name');
        $order->summary_cost      = $order->predv_price;

        $form = app()->user->can('orders') ? 'update' : 'view';

        $positions = $orderService->getPositions($order->city_id);
        $tariffs   = $orderService->getTariffs($order->city_id, $order->position_id, $order->client_id, $order->payment,
            $order->company_id, true, ['operator']);
        $tariff    = $orderService->getTariff($order->tariff_id);
        $tariffs   = array_merge([$tariff], $tariffs);

        $orderActions        = array_map(function ($value) {
            return [$value => t('order', $value)];
        }, $actionManager->getAvailableActions($order->status_id));
        $availableAttributes = $actionManager->getAvailableAttributes($order->status_id);

        $clientInfo = $orderService->getClientInformationById($order->tenant_id, $order->city_id, $order->client_id);
        $workerInfo = $orderService->getWorkerInformation($order->tenant_id, $order->worker_id, $order->car_id,
            $order->city_id, $order->position_id);

        $canBonusPayment = $this->canBonusPayment($order->tenant_id, $order->client_id, $order->city_id,
            $order->tariff_id);


        return $this->renderAjax('@app/modules/order/views/order/' . $form, [
            'order'               => $order,
            'dataForm'            => $order->getFormData(null, $order->status_id),
            'parkingList'         => $order->getAddressParklingList(),
            'positions'           => $positions,
            'tariffs'             => $tariffs,
            'orderActions'        => $orderActions,
            'availableAttributes' => $availableAttributes,
            'clientInfo'          => $clientInfo,
            'workerInfo'          => $workerInfo,
            'canBonusPayment'     => $canBonusPayment,
        ]);
    }

    /**
     * View order page by id or order number.
     *
     * @param integer $order_number
     *
     * @return mixed
     * @throws \yii\base\InvalidParamException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionView($order_number)
    {
        $orderNumber = filter_var($order_number, FILTER_SANITIZE_NUMBER_INT);

        $orderObj = $this->getOrderViewObject($orderNumber);

        /** @var $orderService OrderService */
        $orderService = $this->module->orderService;

        if (app()->request->isPost) {
            app()->response->format = Response::FORMAT_JSON;

            try {
                $orderObj->load(app()->request->post());

                if ($orderObj->orderAction === ActionManager::ACTION_COPY_ORDER) {
                    app()->session->set('copyOrder', $orderObj->order_id);

                    return OrderRequestResult::getResult(
                        OrderRequestResult::ACTION_REDIRECT, '', '/create?city_id=' . $orderObj->city_id);
                }

                return OrderRequestResult::getResult(
                    OrderRequestResult::ACTION_ERROR,
                    t('order', 'Invalid order action.')
                );
            } catch (\Exception $ex) {
                \Yii::error($ex, 'order');

                return OrderRequestResult::getResult(
                    OrderRequestResult::ACTION_ERROR,
                    t('order', 'Error saving order. Notify to administrator, please.')
                );
            }
        }

        $clientInfo = $orderService->getClientInformationById($orderObj->tenant_id, $orderObj->city_id,
            $orderObj->client_id);
        $workerInfo = $orderService->getWorkerInformation($orderObj->tenant_id, $orderObj->worker_id, $orderObj->car_id,
            $orderObj->city_id, $orderObj->position_id);

        $orderActions = array_map(function ($value) {
            return [$value => t('order', $value)];
        }, [ActionManager::ACTION_COPY_ORDER]);

        $positions = $orderService->getPositions($orderObj->city_id);
        $tariffs   = $orderService->getTariffs($orderObj->city_id, $orderObj->position_id, $orderObj->client_id,
            $orderObj->payment, $orderObj->company_id, false, ['operator']);

        $view = '@app/modules/order/views/order/view';

        $params = [
            'order'               => $orderObj,
            'dataForm'            => $orderObj->getFormData(null, $orderObj->status_id),
            'parkingList'         => $orderObj->getAddressParklingList(),
            'positions'           => $positions,
            'tariffs'             => $tariffs,
            'clientInfo'          => $clientInfo,
            'workerInfo'          => $workerInfo,
            'orderActions'        => $orderActions,
            'availableAttributes' => [],
        ];

        if (app()->request->isAjax) {
            return $this->renderAjax($view, $params);
        } else {
            return $this->render($view, $params);
        }
    }

    protected function getOrderViewObject($order_number)
    {
        $order = Order::getOrderInfo($order_number);

        if (empty($order)) {
            throw new NotFoundHttpException();
        }

        $order->additional_option = ArrayHelper::map($order->options, 'option_id', 'name');

        return $order;
    }

    /**
     * Getting bonus balance
     *
     * @param $clientId
     * @param $currencyId
     *
     * @return float
     */
    public function getBonusBalance($clientId, $currencyId)
    {
        try {
            $bonusSystem = \Yii::createObject(BonusSystem::class, [user()->tenant_id]);

            return $bonusSystem->getBalance($clientId, $currencyId);
        } catch (\yii\base\Exception $ex) {
            return 0;
        }
    }

    /**
     * Getting client full name by phone.
     *
     * @param string $phone
     *
     * @return json
     */
    public function actionGetClient($phone, $city_id, $showCardPayment = false)
    {
        if (!Yii::$app->request->isAjax) {
            return false;
        }

        $json      = [];
        $client    = ClientSearch::searchByPhone($phone);
        $companies = null;

        if (!empty($client)) {
            $companies = $client->companies;

            $json['client_id']  = $client->client_id;
            $json['black_list'] = $client->black_list;
            $json['client']     = Html::a(Html::encode($client->getShortName()), [
                '/client/base/update',
                'id' => $client->client_id,
            ]);

            $currencyId     = TenantSetting::getSettingValue(
                user()->tenant_id, DefaultSettings::SETTING_CURRENCY, $city_id);
            $currencySymbol = Currency::getCurrencySymbol($currencyId);
            $account        = $client->getAccountByCurrencyId($currencyId);
            $bonusBalance   = $this->getBonusBalance($client->client_id, $currencyId);

            $json['client_more_info'] = $this->renderPartial('_clientMoreInfo', [
                'successOrders' => +$client->success_order,
                'failedOrders'  => $client->fail_worker_order + $client->fail_client_order,
                'clientBalance' => app()->formatter->asMoney(
                    empty($account->balance) ? 0 : +$account->balance, $currencySymbol),
                'bonusBalance'  => app()->formatter->asMoney($bonusBalance,
                    t('currency', 'B') . '(' . $currencySymbol . ')'),
            ]);
        }

        $json['payment_html_select'] = (new Order())->getPaymentHtmlSelect($companies, false, intVal($showCardPayment));

        return json_encode($json);
    }

    /**
     * Getting worker.
     *
     * @param string  $input_search
     * @param integer $city_id
     *
     * @return json
     */
    public function actionGetWorker($input_search, $city_id)
    {
        if (!Yii::$app->request->isAjax) {
            return false;
        }

        $workers = [];
        $db_cabs = \Yii::$app->redis_workers->executeCommand('hvals', [user()->tenant_id]);

        foreach ($db_cabs as $cab) {
            $cab = unserialize($cab);

            if ($cab['worker']['status'] != 'FREE' || $cab['worker']['city_id'] != $city_id) {
                continue;
            }

            if (mb_stripos($cab['worker']['last_name'], $input_search) !== false ||
                mb_stripos($cab['worker']['name'], $input_search) !== false ||
                mb_stripos($cab['worker']['second_name'], $input_search) !== false ||
                stripos($cab['worker']['callsign'], $input_search) !== false ||
                stripos($cab['car']['name'], $input_search) !== false ||
                stripos($cab['car']['gos_number'], $input_search) !== false
            ) {
                /** @var WorkerService $workerService */
                $workerService = Yii::$app->getModule('employee')->get('worker');
                $workers[]     = [
                    'worker_id' => $cab['worker']['worker_id'],
                    'name'      => $workerService->getShortName($cab['worker']['last_name'],
                        $cab['worker']['name'], $cab['worker']['second_name']),
                    'callsign'  => $cab['worker']['callsign'],
                    'car'       => [
                        'name'       => $cab['car']['name'],
                        'color'      => CarColor::getColorText($cab['car']['color']),
                        'gos_number' => $cab['car']['gos_number'],
                    ],
                ];
            }
        }

        return $this->renderAjax('worker_search', ['workers' => $workers]);
    }

    /**
     * Add a new address in order card.
     * @return html
     */
    public function actionGetNewAddress()
    {
        $parking = ArrayHelper::map(
            Parking::getParking(user()->tenant_id, post('city_id')), 'parking_id', 'name');

        return $this->renderAjax(
            '@app/modules/order/views/order/new_address',
            [
                'char'      => post('char'),
                'city_id'   => post('city_id'),
                'city_name' => post('city_name'),
                'parking'   => $parking,
            ]
        );
    }

    /**
     * A preliminary calculation of the order.
     * @return json
     * @expectedException \Geocoder\Exception\ChainNoResultException
     */
    public function actionRouteAnalyzer()
    {
        if (!Yii::$app->request->isAjax) {
            return false;
        }

        $post  = post('Order');
        $order = Order::findOne([
            'order_number' => $post['order_number'],
            'tenant_id'    => user()->tenant_id,
        ]);
        if (!$order) {
            $order = new Order();
        }
        $order->load(post());

        //Нет базовой парковки
        $error_no_base_parking = 100;

        if ($order->order_now == 0) {
            $datetime = $order->order_date . ' ' . $order->order_hours . ':' . $order->order_minutes;
            $time     = Yii::$app->formatter->asTimestamp($datetime);
        } else {
            $time = time() + $order->getOrderOffset();
        }

        $routeAnalyzer = app()->routeAnalyzer;
        $addressArray  = array_values($order->addressFilter($order->address));

        $result = $routeAnalyzer->analyzeRoute(user()->tenant_id, $order->city_id, $addressArray,
            $order->additional_option, $order->tariff_id, date('d.m.Y H:i:s', $time));

        if (empty($result)) {
            $result['error'] = $error_no_base_parking;
        }

        app()->response->headers->add('request-time',
            app()->request->headers->get('request-time'));

        return json_encode($result);
    }

    public function actionOrderRefresh($controller = null)
    {
        if (!Yii::$app->request->isAjax) {
            return false;
        }

        $controller         = is_null($controller) ? $this->id : $controller;
        $city_id            = post('city_id');
        $group              = post('group');
        $workerShiftService = $this->getWorkerShiftService();
        $json               = ['workers' => $workerShiftService->getCntWorkersOnShift($city_id),];


        if (!empty($group)) {
            $arFilter = post('filter');

            if (!empty($city_id)) {
                $arFilter['city_id'] = $city_id;
            }

            $groupsFromRedis = OrderStatus::getGroupsFromRedis();


            if (in_array($group, $groupsFromRedis)) {
                $attr   = post('attr', 'order_id');
                $orders = Order::getOrdersFromRedis($group, $arFilter, post('sort'), $attr);
                if ($group == OrderStatus::STATUS_GROUP_7) {
                    $not_orders = ArrayHelper::getColumn($orders, 'order_id');
                    $orders     = array_merge($orders,
                        Order::getByStatusGroup($group, $city_id, null, [], $arFilter['status'], $not_orders));
                }
            } else {
                $orders = Order::getByStatusGroup($group, $city_id, null,
                    ['sort' => post('sort'), 'order' => post('orderBy')], $arFilter['status']);
            }

            $json['count'] = $this->orderService->getCountOrdersForTabs($arFilter);

            $json['html'] = $this->renderAjax('@app/modules/order/views/order/' . post('view'), [
                'orders'     => $orders,
                'controller' => $controller,
            ]);
        }

        return json_encode($json);
    }

    public function actionSort($type, $city_id = null, $sourse = 'redis', $order = 'order_id')
    {
        if (!Yii::$app->request->isAjax) {
            return false;
        }

        $arFilter = post('filter');

        if (!empty($city_id)) {
            $arFilter['city_id'] = $city_id;
        }
        $attr = post('attr', 'order_time');
        if ($sourse == 'redis') {
            $orders = Order::getOrdersFromRedis($type, $arFilter, post('sort'), $attr);
        } else {
            $orders = Order::getByStatusGroup($type, $city_id, null, ['sort' => $attr, 'order' => $order],
                $arFilter['status']);
        }

        return $this->renderAjax("@app/modules/order/views/order/_$type", [
            'orders' => $orders,
        ]);
    }

    public function actionGetEvents($order_id, $city_id)
    {
        if (!Yii::$app->request->isAjax) {
            return false;
        }

        //Результирующий массив
        $events = [];
        //Доп. информация по логу (Расчет стоимости, Отзыв)
        $info = [];
        //Получаем события из БД
        $db_events = OrderChangeData::find()->asArray()->where(['order_id' => $order_id])->all();
        //Смещение часового пояса
        $city_offset = City::getTimeOffset($city_id);
        //Берем из кеша все статусы, формируем карту
        $arStatus = ArrayHelper::map(OrderStatus::getStatusData(), 'status_id', 'name');
        //Кеш запросов
        $arCache        = [];
        $count          = count($db_events);
        $order_finished = false;
        $formatter      = app()->formatter;
        $lastStatusId   = null;

        $order      = Order::findOne($order_id);
        $positionId = isset($order->position_id) ? $order->position_id : null;


        $currencyId = ArrayHelper::getValue($order, 'currency_id');

        $currencySymbol = Html::encode(Currency::getCurrencySymbol($currencyId));


        for ($i = 0; $i < $count; $i++) {
            $text = '';
            $info = [];

            if ($db_events[$i]['change_object_type'] == 'order'
                && $db_events[$i]['change_field'] == 'status_id'
            ) {
                $lastStatusId = $db_events[$i]['change_val'];
            }

            //Первый элемент - Создание заказа
            if ($i == 0 && $db_events[$i]['change_object_type'] == 'order' && $db_events[$i]['change_field'] == 'status_id') {

                if (in_array($db_events[$i]['change_val'], [
                    OrderStatus::STATUS_NEW,
                    OrderStatus::STATUS_NOPARKING,
                    OrderStatus::STATUS_PRE,
                    OrderStatus::STATUS_PRE_NOPARKING,
                    OrderStatus::STATUS_FREE,
                    OrderStatus::STATUS_MANUAL_MODE,
                ], false)) {
                    $text = t('order', 'Order create');
                } elseif ($db_events[$i]['change_val'] == OrderStatus::STATUS_EXECUTING) {
                    $time     = implode(" ", [
                        $formatter->asDate($city_offset + $db_events[$i]['change_time'], 'shortDate'),
                        t('order', 'on'),
                        $formatter->asTime($city_offset + $db_events[$i]['change_time'], 'medium'),
                    ]);
                    $events[] = [
                        'TEXT'         => t('order', 'Order was created from bordur'),
                        'TIME'         => $time,
                        'TYPE'         => $db_events[$i]['change_object_type'],
                        'CHANGE_FIELD' => $db_events[$i]['change_field'],
                        'INFO'         => $info,
                    ];

                    $text = OrderStatusService::translate($db_events[$i]['change_val'], $positionId);
                }
            } //Изменения с заказом
            elseif ($db_events[$i]['change_object_type'] === 'order') {

                //Изменения клиента
                if (strpos($db_events[$i]['change_subject'], 'client') !== false) {

                    if ($db_events[$i]['change_field'] === 'address' || $db_events[$i]['change_field'] === 'payment') {

                        $arClientPieces = explode('_', $db_events[$i]['change_subject']);
                        $client_id      = end($arClientPieces);

                        $client = Client::find()
                            ->alias('c')
                            ->where(['c.client_id' => $client_id])
                            ->joinWith('clientPhones cp')
                            ->one();

                        $name = '<a href="' . Url::to([
                                '/client/base/update',
                                'id' => $client_id,
                            ]) . '">' . Html::encode(($client->last_name !== '' || $client->name !== '' || $client->second_name !== '') ? ($client->last_name . ' ' . $client->name . ' ' . $client->second_name) : $client->clientPhones[0]->value) . '</a>';

                        $text = t('order', 'Client {name} edited order. That is: {field}', [
                            'name'  => $name,
                            'field' => Html::encode($order->getAttributeLabel($db_events[$i]['change_field'])),
                        ]);

                    }
                    //Изменения диспетчера
                } elseif (strpos($db_events[$i]['change_subject'], 'disputcher') !== false) {
                    //Пропускаем служебные поля, которые не должен видеть пользователь
                    if ($db_events[$i]['change_field'] === 'client_id') {
                        continue;
                    }
                    $arUserPieces = explode('_', $db_events[$i]['change_subject']);
                    $user_id      = end($arUserPieces);

                    $user = \app\modules\tenant\models\User::find()->
                    where(['user_id' => $user_id])->
                    select(['last_name', 'name'])->
                    one();
                    $name = '<a href="' . Url::to([
                            '/tenant/user/update',
                            'id' => $user_id,
                        ]) . '">' . Html::encode($user->last_name . ' ' . $user->name) . '</a>';

                    $order = new Order();

                    if ($db_events[$i]['change_field'] === 'status_id'
                        && in_array($db_events[$i]['change_val'], OrderStatus::getCompletedStatusId(), false)
                    ) {
                        $text = t('order', 'The order was completed by the dispatcher {name}.', [
                            'name' => $name,
                        ]);
                    } else {
                        $text = t('order', 'Disputcher {name} edited order. That is: {field}', [
                            'name'  => $name,
                            'field' => Html::encode($order->getAttributeLabel($db_events[$i]['change_field'])),
                        ]);
                    }
                } //Изменения водителя
                elseif ($db_events[$i]['change_subject'] === 'worker' &&
                    ($db_events[$i]['change_field'] === 'address' || $db_events[$i]['change_field'] === 'predv_price')
                ) {
                    $order = new Order();
                    $text  = t('order', 'Worker edited order. That is: {field}', [
                        'field' => Html::encode($order->getAttributeLabel($db_events[$i]['change_field'])),
                    ]);
                } //Все остальные, связанные со сменой статуса
                elseif ($db_events[$i]['change_field'] === 'status_id') {
                    if (in_array($db_events[$i]['change_val'], OrderStatus::getFinishedStatusId(), false)
                        || $db_events[$i]['change_val'] == OrderStatus::STATUS_EXECUTING
                        || $db_events[$i]['change_val'] == OrderStatus::STATUS_WAITING_FOR_PAYMENT
                    ) {
                        $order_finished = true;
                    }
                    //Статус завершенного заказа
                    if (in_array($db_events[$i]['change_val'], OrderStatus::getCompletedStatusId(), false)) {
                        $text = OrderStatusService::translate($db_events[$i]['change_val'], $positionId);
                        $info = OrderDetailCost::find()
                            ->where(['order_id' => $db_events[$i]['order_id']])
                            ->orderBy(['detail_id' => SORT_DESC])
                            ->asArray()
                            ->one();
                    } //Пропускаем статус "Предложение водителю", т.к. уже вывели эту инфу вместе с водилой.
                    elseif (in_array($db_events[$i]['change_val'], [
                        OrderStatus::STATUS_OFFER_ORDER,
                        OrderStatus::STATUS_WORKER_REFUSED,
                        OrderStatus::STATUS_WORKER_IGNORE_ORDER_OFFER,
                    ], false)) {
                        continue;
                    } //Все остальные статусы, название берется из карты статусов
                    else {
                        $text = OrderStatusService::translate($db_events[$i]['change_val'], $positionId);
                        if ((int)$db_events[$i]['change_val'] === OrderStatus::STATUS_PAYMENT_CONFIRM) {
                            $priorEvent = isset($db_events[$i - 1]) ? $db_events[$i - 1] : null;

                            if ($priorEvent !== null
                                && $priorEvent['change_object_type'] === 'order'
                                && $priorEvent['change_subject'] === 'worker'
                                && $priorEvent['change_field'] === 'payment'
                                && $priorEvent['change_val'] === Order::PAYMENT_CASH
                            ) {
                                $text .= '. ' . t('order', 'Error of non-cash payment (requires payment in cash)');
                            }
                        }
                    }
                }
            } //Изменения с исполнителем
            elseif ($db_events[$i]['change_object_type'] == 'worker') {
                //Формирование кеша для того чтобы не делать лишние запросы на след. эл. массива лога
                if (empty($arCache['worker'][$db_events[$i]['change_object_id']])) {
                    $workerService = $this->getWorkerService();

                    $dbWorker = $workerService->getWorkerByCallsign($db_events[$i]['change_object_id'],
                        $db_events[$i]['tenant_id'], ['worker_id', 'last_name', 'name']);

                    $arCache['worker'][$db_events[$i]['change_object_id']] = '<a href="' . Url::to([
                            '/employee/worker/update',
                            'id' => $dbWorker['worker_id'],
                        ]) . '">' . Html::encode($dbWorker['last_name'] . ' ' . $dbWorker['name']) . '</a>';
                }

                //Предложение заказа
                if ($db_events[$i]['change_val'] == 'OFFER_ORDER') {
                    $text = t('order', 'Order offer worker: {name}',
                        ['name' => $arCache['worker'][$db_events[$i]['change_object_id']]]);
                } elseif ($db_events[$i]['change_val'] == 'FREE' && !$order_finished) {
                    switch ($lastStatusId) {
                        case OrderStatus::STATUS_WORKER_REFUSED:
                            $message = 'Worker {name} refused';
                            break;
                        case OrderStatus::STATUS_WORKER_IGNORE_ORDER_OFFER:
                            $message = 'Worker {name} ignored order offer';
                            break;
                        default:
                            $message = 'Worker {name} removed from the order';
                    }
                    $text = t('order', $message,
                        ['name' => $arCache['worker'][$db_events[$i]['change_object_id']]]);
                } //Исполнитель заблокирован
                elseif ($db_events[$i]['change_val'] == 'BLOCKED') {
                    $text = t('order', 'Worker {name} blocked',
                        ['name' => $arCache['worker'][$db_events[$i]['change_object_id']]]);
                } //Взял заказ
                elseif ($db_events[$i]['change_val'] == 'ON_ORDER') {
                    //Для получения времени подъезда берем следующий эл. массива
                    $nextAfterElement = $db_events[$i + 1];
                    $time             = $nextAfterElement['change_field'] == 'time_to_client' ? $nextAfterElement['change_val'] : 0;

                    $text = t('order', 'Worker {name} accept an order and arrive in {time} min.',
                        ['name' => $arCache['worker'][$db_events[$i]['change_object_id']], 'time' => $time]);

                    //Удаляем элементы, т.к. мы их уже обработали
                    if (($db_events[$i + 2]['change_field'] == 'status_id')
                        && ($db_events[$i + 2]['change_val'] == OrderStatus::STATUS_GET_WORKER
                            || $db_events[$i + 2]['change_val'] == OrderStatus::STATUS_EXECUTION_PRE)
                    ) {
                        unset($db_events[$i + 2]);
                    }

                    if ($db_events[$i + 1]['change_field'] == 'time_to_client') {
                        unset($db_events[$i + 1]);
                    }
                    // worker accepted an pre-order
                } elseif ($db_events[$i]['change_val'] == 'ACCEPT_PREORDER') {
                    $text = t('order', 'Worker {name} accepted an pre-order', [
                        'name' => $arCache['worker'][$db_events[$i]['change_object_id']],
                    ]);
                    if ($db_events[$i + 1]['change_field'] == 'status_id'
                        && $db_events[$i + 1]['change_val'] == OrderStatus::STATUS_PRE_GET_WORKER
                    ) {
                        unset($db_events[$i + 1]);
                    }
                    // worker refused an pre-order
                } elseif ($db_events[$i]['change_val'] == 'REFUSE_PREORDER') {
                    $text = t('order', 'Worker {name} refused an pre-order', [
                        'name' => $arCache['worker'][$db_events[$i]['change_object_id']],
                    ]);
                    if ($db_events[$i + 1]['change_field'] == 'status_id'
                        && $db_events[$i + 1]['change_val'] == OrderStatus::STATUS_PRE_REFUSE_WORKER
                    ) {
                        unset($db_events[$i + 1]);
                    }
                }
            } //Оставлен отзыв к заказу
            elseif ($db_events[$i]['change_object_type'] == 'review') {
                $clientReview = ClientReview::find()->
                with([
                    'client' => function ($query) {
                        $query->select(['client_id', 'last_name', 'name']);
                    },
                ])->
                where(['review_id' => $db_events[$i]['change_object_id']])->
                one();

                $clientName = $clientReview->client->last_name . ' ' . $clientReview->client->name;

                $text = t('order', 'Client {name} add a review about the trip:', [
                    'name' => '<a href="' . Url::to([
                            '/client/base/update',
                            'id' => $clientReview->client->client_id,
                        ]) . '">' . Html::encode($clientName) . '</a>',
                ]);

                $info = [
                    'REVIEW'  => $clientReview->text,
                    'RAITING' => $clientReview->rating,
                ];
            }

            //Формирование результирующего массива
            if (!empty($text)) {
                $time     = implode(" ", [
                    $formatter->asDate($city_offset + $db_events[$i]['change_time'], 'shortDate'),
                    t('order', 'on'),
                    $formatter->asTime($city_offset + $db_events[$i]['change_time'], 'medium'),
                ]);
                $events[] = [
                    'TEXT'         => t('status_event', $text),
                    'TIME'         => $time,
                    'TYPE'         => $db_events[$i]['change_object_type'],
                    'CHANGE_FIELD' => $db_events[$i]['change_field'],
                    'INFO'         => $info,
                ];
            }
        }
        $raw_cacl_data = (new \yii\db\Query())
            ->select(['raw_cacl_data'])
            ->from('tbl_raw_order_calc')
            ->where(['order_id' => $order_id])
            ->scalar();

        $raw_cacl_data = !empty($raw_cacl_data) ? json_decode($raw_cacl_data, true) : null;


        $info = OrderDetailCost::find()
            ->where(['order_id' => $order_id])
            ->orderBy(['detail_id' => SORT_DESC])
            ->asArray()
            ->one();

        if (!empty($info)) {

            $getInstanceHelper = function ($byCity) use ($info, $currencySymbol) {
                return TariffCost::getInstance([
                    'info'           => $info,
                    'currencySymbol' => $currencySymbol,
                    $byCity          => $info[$byCity],
                ]);
            };

            $inCity = $getInstanceHelper('accrual_city');

            $outCity = $getInstanceHelper('accrual_out');


            $strategy = new PriceCalculationStrategy();

            $service = new PriceCalculationService($strategy);

            $promoBonusOperation = PromoBonusOperation::find($info['order_id'])->one();

            $info['refill_promo_bonus'] = $promoBonusOperation->promo_bonus;

            $priceCalculation = new PriceCalculation($inCity,
                $outCity, $info, ['minute' => t('app', 'min.'), 'km' => t('app', 'km')],
                $currencySymbol, $service);

            $service->setModel($priceCalculation);

            $strategy->setModel($priceCalculation);


            $priceCalculation->setPriceInCityByAccrual();

            $priceCalculation->setPriceOutCityByAccrual();

        }


        return $this->renderAjax('@app/modules/order/views/order/events',
            compact('events', 'currencySymbol', 'raw_cacl_data', 'order_id', 'priceCalculation'));
    }

    /**
     * Determining the type of data displayed on the map. Loading view with map.
     *
     * @param integer $order_id Used in the view like GET param
     * @param integer $status_id Order field status_id
     *
     * @return json
     */
    public function actionGetMap($order_id, $status_id)
    {
        if (!Yii::$app->request->isAjax) {
            return false;
        }

        return $this->getMap($status_id);
    }

    protected function getMap($status_id)
    {
        $map_data_type = Order::MAP_DATA_TYPE_ADDRESS;
        $status_map    = ArrayHelper::map(OrderStatus::getStatusData(), 'status_id', 'status_group');

        if (isset($status_map[$status_id])) {
            if (in_array($status_map[$status_id], OrderStatus::getStatusGroupsForMapWorkerType())) {
                $map_data_type = Order::MAP_DATA_TYPE_WORKER;
            } elseif (in_array($status_map[$status_id], OrderStatus::getStatusGroupsForMapRouteType())) {
                $map_data_type = Order::MAP_DATA_TYPE_ROUTE;
            }
        }

        return $this->renderAjax('@app/modules/order/views/order/tracking', ['map_data_type' => $map_data_type]);
    }

    /**
     * If successful returns encoded string
     * else boolean false
     *
     * @param int $city_id
     *
     * @return json
     */
    public function actionTariffAjaxUpdate($city_id = null)
    {
        if (!Yii::$app->request->isAjax) {
            return false;
        }

        return json_encode(TaxiTariff::getTenantTariffList($city_id));
    }

    /**
     * Getting order address coords
     *
     * @param int $order_id
     *
     * @return json
     */
    public function actionGetOrderAddress($order_id)
    {
        if (!Yii::$app->request->isAjax) {
            return false;
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $address = Order::getOrderAddress($order_id);

        return $address ? $address : ['error' => t('order', 'No data for display')];
    }

    /**
     * Getting order tracking coords.
     *
     * @param integer $order_id
     *
     * @return json
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetTracking($order_id)
    {
        if (!Yii::$app->request->isAjax) {
            return false;
        }

        /* @var $service OrderTrackService */
        $service = \Yii::createObject(OrderTrackService::class);
        $track   = $service->getTrack($order_id);

        if (empty($track)) {
            return json_encode(['error' => t('order', 'No data for display')]);
        } else {
            return json_encode([
                'order_route' => $track,
            ]);
        }
    }

    /**
     * @return WorkerService
     * @throws \yii\base\InvalidConfigException
     */
    protected function getWorkerService()
    {
        return Yii::$app->getModule('employee')->get('worker');
    }

    /**
     * Getting current worker coords by id.
     *
     * @param integer $worker_id
     *
     * @return json
     */
    public function actionGetWorkerCoordsById($worker_id)
    {
        if (!Yii::$app->request->isAjax) {
            return false;
        }

        $json           = ['error' => t('order', 'No data for display')];
        $workerService  = $this->getWorkerService();
        $workerCallsign = $workerService->getCallsign($worker_id);

        $workerShiftService = $this->getWorkerShiftService();

        if (!empty($workerCallsign)) {
            $activeWorker = $workerShiftService->getWorkerLocation($workerCallsign);

            if (!empty($activeWorker)) {
                $json             = $activeWorker;
                $json['callsign'] = $workerCallsign;
                $json['error']    = '';
            }
        }

        return json_encode($json);
    }

    /**
     * Getting current worker coords by callsing.
     *
     * @param integer $callsign
     *
     * @return json
     */
    public function actionGetWorkerCoordsByCallsign($callsign)
    {
        if (!Yii::$app->request->isAjax) {
            return false;
        }

        $workerShiftService = $this->getWorkerShiftService();

        return json_encode($workerShiftService->getWorkerLocation($callsign));
    }

    public function actionCheckCompanyTariff()
    {
        if (!Yii::$app->request->isAjax) {
            return false;
        }

        $json             = 0;
        $companyHasTariff = ClientCompanyHasTariff::findAll(['company_id' => post('company_id')]);

        if (!empty($companyHasTariff)) {
            $arClassTariff = ArrayHelper::getColumn($companyHasTariff, 'tariff_id');
            $tariff        = TaxiTariff::findOne(['tariff_id' => post('tariff_id')]);

            if (in_array($tariff->class_id, $arClassTariff)) {
                $json = 1;
            }
        }

        return json_encode($json);
    }

    public function actionGetWorkersOnParkings($status, $city_id = null)
    {
        if (!Yii::$app->request->isAjax) {
            return false;
        }

        $workerShiftService = $this->getWorkerShiftService();

        return $this->renderAjax('workersOnParking',
            ['workers' => $workerShiftService->getCntWorkersOnParkings($status, $city_id)]);
    }

    public function actionClientReview($order_id = null)
    {
        $review = ClientReview::findOne(['order_id' => $order_id]);

        return $this->renderAjax('@app/modules/order/views/order/client_review', [
            'review' => $review,
        ]);
    }

    /**
     * Return current time in city (+pickup)
     *
     * @param city_id
     *
     * @return array
     */
    public function actionGetCurrentTime()
    {
        app()->response->format = Response::FORMAT_JSON;

        $order = new Order;
        $date  = time() + City::getTimeOffset(post('city_id'))
            + $order->getPickUp() + post('offset') * 60;

        return [
            'date'    => date('Y-m-d', $date),
            'hours'   => date('H', $date),
            'minutes' => date('i', $date),
        ];
    }

    /**
     * Is there a client bonus
     *
     * @param string  $phone
     * @param int     $cityId
     * @param integer $tariffId
     *
     * @return bool
     */
    public function actionCanBonusPayment($phone, $cityId, $tariffId)
    {
        if (!app()->request->isAjax) {
            return false;
        }

        app()->response->format = Response::FORMAT_JSON;

        $client   = ClientSearch::searchByPhone($phone);
        $tenantId = user()->tenant_id;

        return empty($client) ? false
            : $this->canBonusPayment($tenantId, $client['client_id'], $cityId, $tariffId);
    }

    /**
     * Can update order?
     *
     * @param int $tenantId
     * @param int $clientId
     * @param int $cityId
     * @param int $tariffId
     *
     * @return bool
     */
    protected function canBonusPayment($tenantId, $clientId, $cityId, $tariffId)
    {
        try {
            $currencyId = TenantSetting::getSettingValue($tenantId, DefaultSettings::SETTING_CURRENCY, $cityId);

            $bonusSystem = $bonusSystem = \Yii::createObject(BonusSystem::class, [$tenantId]);
            if ($bonusSystem->isUDSGameBonusSystem()) {
                return false;
            }

            $paymentStrategy = $bonusSystem->getPaymentStrategy($tariffId);
            $balance         = $bonusSystem->getBalance($clientId, $currencyId);

            return $paymentStrategy !== null && $balance > 0;
        } catch (\yii\base\Exception $ex) {
            return false;
        }
    }

    /**
     * Вывести список доп опций
     * @return json
     */
    public function actionAddOptions()
    {
        if (!Yii::$app->request->isAjax) {
            return false;
        }
        app()->response->format = Response::FORMAT_JSON;

        $post  = post('Order');
        $order = Order::findOne([
            'order_number' => $post['order_number'],
            'tenant_id'    => user()->tenant_id,
        ]);
        if (!$order) {
            $order = new Order();
        }
        $order->load(post());

        $tariff_id = $order->tariff_id;

        // Если заказ на определенное время
        if ($order->order_now == 0) {
            $time = strtotime($order->order_date . ' ' . $order->order_hours . ':' . $order->order_minutes);
        } else { // если заказ на сейчас
            $tariff = TaxiTariff::find()
                ->where('tariff_id=:tariff_id', ['tariff_id' => $tariff_id])
                ->one();
            $offset = City::getTimeOffset($tariff->cities[0]->city_id);
            $time   = time() + $offset;
        }
        $routeAnalyzer = app()->routeAnalyzer;
        $addoptions    = $routeAnalyzer->addOptions($tariff_id, date('d.m.Y H:i:s', $time));

        $car_options = CarOption::find()
            ->where(['option_id' => $addoptions])
            ->asArray()
            ->all();
        $result      = [];
        foreach ($car_options as $item) {
            $option_id          = $item['option_id'];
            $result[$option_id] = t('car-options', $item['name']);
        }

        return $result;
    }

    /**
     * Getting position list
     * @return array
     */
    public function actionGetPositionList()
    {
        if (!Yii::$app->request->isAjax) {
            return false;
        }
        app()->response->format = Response::FORMAT_JSON;

        $city_id   = Yii::$app->request->post('city_id');
        $positions = $this->module->orderService->getPositions($city_id);

        return $positions;
    }

    /**
     * Метод возращает список всех тарифов для определенного города и профессии
     * @return array
     */
    public function actionGetTariffList()
    {
        if (!Yii::$app->request->isAjax) {
            return false;
        }
        app()->response->format = Response::FORMAT_JSON;

        $city_id     = post('city_id');
        $position_id = post('position_id');
        $clientId    = post('client_id');
        $payment     = post('payment');
        $companyId   = null;
        if ($this->module->orderService->isCorpBalance($payment)) {
            $company   = explode(Order::PAYMENT_CORP . '_', $payment, 2);
            $payment   = Order::PAYMENT_CORP;
            $companyId = isset($company[1]) ? $company[1] : null;
        }

        $tariffs = $this->module->orderService->getTariffs($city_id, $position_id, $clientId, $payment, $companyId,
            true, ['operator']);
        $tariffs = array_map(function ($item) {
            $item['description'] = str_replace(PHP_EOL, '<br>', Html::encode($item['description']));

            return $item;
        }, $tariffs);

        return $tariffs;
    }

    /**
     * Stop offer to worker
     * @return mixed
     */
    public function actionStopOffer()
    {
        app()->response->format = Response::FORMAT_JSON;
        if (!app()->request->isAjax) {
            return ['code' => 0];
        }

        $tenantId       = user()->tenant_id;
        $userId         = user()->user_id;
        $orderNumber    = post('order_number');
        $lastUpdateTime = post('update_time');
        $lang           = user()->lang;

        $orderId = Order::find()->select('order_id')
            ->where([
                'order_number' => $orderNumber,
                'tenant_id'    => user()->tenant_id,
            ])->scalar();

        try {
            $this->module->orderService->stopOffer($tenantId, $userId, $orderId, $lastUpdateTime, $lang);
            session()->setFlash('success',
                t('order', 'The request to stop offer to worker has been sent'));

            return ['code' => 1];
        } catch (\Exception $ex) {
            return ['code' => 0];
        }
    }

    /**
     * Search clients
     *
     * @param string $query
     *
     * @return array JSON
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function actionSearchClients($query)
    {
        app()->response->format = Response::FORMAT_JSON;

        /* @var $service OrderService */
        $service = \Yii::createObject(OrderService::className());

        $tenantId = user()->tenant_id;

        return $service->searchClients($tenantId, $query);
    }

    /**
     * Getting client information
     *
     * @param string $phone
     * @param int    $cityId
     *
     * @return array JSON
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetClientInfo($phone, $cityId)
    {
        app()->response->format = Response::FORMAT_JSON;

        /* @var $service OrderService */
        $service = \Yii::createObject(OrderService::className());

        $tenantId = user()->tenant_id;

        $info = $service->getClientInformationByPhone($tenantId, $cityId, $phone);
        if ($info === null) {
            throw new NotFoundHttpException('Client not found');
        } else {
            return $info;
        }
    }

    /**
     * Search worker
     *
     * @param string $query
     * @param int    $cityId
     * @param int    $positionId
     *
     * @return array JSON
     * @throws \yii\base\InvalidConfigException
     */
    public function actionSearchWorkers($query, $cityId, $positionId)
    {
        app()->response->format = Response::FORMAT_JSON;

        /* @var $service OrderService */
        $service = \Yii::createObject(OrderService::className());

        $tenantId = user()->tenant_id;

        return $service->searchWorkers($tenantId, $cityId, $positionId, $query);
    }

    /**
     * Search worker
     *
     * @param int   $cityId
     * @param int   $positionId
     * @param float $lat
     * @param float $lon
     *
     * @return array JSON
     * @throws \yii\base\InvalidConfigException
     */
    public function actionNearWorkers($cityId, $positionId, $lat, $lon)
    {
        app()->response->format = Response::FORMAT_JSON;

        /* @var $service OrderService */
        $service = \Yii::createObject(OrderService::className());

        $tenantId = user()->tenant_id;

        return $service->searchWorkers($tenantId, $cityId, $positionId, $query = null, $lat, $lon);
    }

    /**
     * Getting worker information
     *
     * @param int $workerId
     * @param int $carId
     * @param int $cityId
     * @param int $positionId
     *
     * @return array JSON
     * @throws \yii\base\InvalidParamException
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     *
     */
    public function actionGetWorkerInfo($workerId, $carId, $cityId, $positionId)
    {
        app()->response->format = Response::FORMAT_JSON;

        /* @var $service OrderService */
        $service = \Yii::createObject(OrderService::className());

        $tenantId = user()->tenant_id;

        $info = $service->getWorkerInformation($tenantId, $workerId, $carId, $cityId, $positionId);
        if ($info === null) {
            throw new NotFoundHttpException('Worker not found');
        } else {
            return $info;
        }
    }

}
