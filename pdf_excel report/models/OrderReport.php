<?php

namespace app\modules\reports\models\orders;

use common\helpers\CacheHelper;
use common\modules\city\models\City;
use common\services\OrderStatusService;
use Yii;
use yii\mongodb\ActiveRecord;
use common\components\gearman\Gearman;
use app\modules\parking\models\Parking;
use app\modules\tariff\models\TaxiTariff;
use app\modules\order\models\OrderStatus;
use app\modules\order\models\Order;
use yii\helpers\ArrayHelper;

/**
 * Class OrderReport
 * @package app\modules\reports\models\orders
 *
 * @property array $statistics
 * @property string $city_id
 * @property string $date
 * @property string $tenant_id
 * @property string $position_id
 * @property int $timestamp
 */
class OrderReport extends ActiveRecord
{
    /**
     * Кол-во знаков после запятой при выводе процентов
     */
    const PERCENT_DIGIT = 0;
    /**
     * Включить/выключить кеширование отчета.
     */
    const STAT_CACHE = false;

    /**
     * @return string the name of the index associated with this ActiveRecord class.
     */
    public static function collectionName()
    {
        return 'order_stat';
    }

    /**
     * @return array list of attribute names.
     */
    public function attributes()
    {
        return ['_id', 'statistics', 'city_id', 'timestamp', 'date', 'tenant_id', 'position_id', 'tenant_company_id'];
    }

    /**
     * Добавляем статистику через Gearman
     * @param integer $order_id
     */
    public static function addStatistic($order_id)
    {
        Yii::$app->gearman->doBackground(Gearman::ORDER_STATISTIC, ['order_id' => $order_id]);
    }

    public static function generateStat($limit, $tenant_id)
    {
        $orders = Order::find()
            ->where(['tenant_id' => $tenant_id])
            ->select(['order_id', Order::tableName() . '.status_id'])
            ->joinWith([
                'status' => function ($query) {
                    $query->where(['status_group' => ['completed', 'rejected', 'new']]);
                },
            ], false)
            ->limit($limit)
            ->orderBy(['order_id' => SORT_DESC])
            ->asArray()
            ->all();

        foreach ($orders as $order) {
            self::addStatistic($order['order_id']);
        }
    }

    /**
     * Получение заказов за определенный период с кешированием.
     * @param integer $city_id
     * @param array $arPeriod ['first_timestamp', 'last_timestamp']
     * @param null|array $arPositionId Position list
     * @return array
     */
    public static function getOrders($city_id, $arPeriod, $arPositionId = null)
    {
        if (self::STAT_CACHE) {
            $key = 'order_report_orders' . user()->tenant_id . '_' . $city_id . '_' . $arPeriod[0] . '_' . $arPeriod[1];
            if (!empty($arPositionId)) {
                $key .= '_' . implode('_', $arPositionId);
            }

            return CacheHelper::getFromCache($key, function () use ($city_id, $arPeriod, $arPositionId) {
                return self::getOrderData($city_id, $arPeriod, $arPositionId);
            });
        }

        return self::getOrderData($city_id, $arPeriod, $arPositionId);

    }

    /**
     * Получение заказов за определенный период.
     * @param integer $city_id City id
     * @param array $arPeriod
     * @param null|array $arPositionId Position list
     * @return array
     */
    private static function getOrderData($city_id, $arPeriod, $arPositionId = null)
    {
        list($first_date, $last_date) = $arPeriod;

        $query = Order::find();
        $query->where([
            'tenant_id' => user()->tenant_id,
            'city_id'   => $city_id,
        ]);

        $query->andFilterWhere(['position_id' => $arPositionId]);

        $tbl_order_status = OrderStatus::tableName();

        $query->innerJoin($tbl_order_status, Order::tableName() . '.status_id=' . $tbl_order_status . '.status_id');
        $query->andWhere([
            $tbl_order_status . '.status_group' => [
                OrderStatus::STATUS_GROUP_4,
                OrderStatus::STATUS_GROUP_5,
            ],
        ]);
        $query->andWhere(['between', 'status_time', $first_date, $last_date]);
        $query->with([
            'userCreated' => function ($sub_query) {
                $sub_query->select(['user_id', 'last_name', 'name']);
            },
            'client'      => function ($sub_query) {
                $sub_query->select(['client_id', 'last_name', 'name']);
            },
            'status',
            'worker'      => function ($sub_query) {
                $sub_query->select(['worker_id', 'last_name', 'name', 'callsign', 'phone']);
            },
            'car'         => function ($sub_query) {
                $sub_query->select(['car_id', 'name', 'gos_number', 'color']);
            },
        ]);
        $query->orderBy(['order_id' => SORT_DESC]);
        $orders = $query->all();

        return empty($orders) ? [] : $orders;
    }

    /**
     * Поиск статиcтики за конкретный период.
     * Исторические данные кешируются.
     * @param integer $city_id
     * @param array $arPeriod ['first_timestamp', 'last_timestamp']
     * @param null|array $arPositionId Position list
     * @param null|integer[] $tenantCompanyIds
     * @return array
     */
    public static function getOrderStat($city_id, $arPeriod, $arPositionId = null, $tenantCompanyIds = null)
    {
        list($first_date, $last_date) = $arPeriod;

        if ($last_date < time() && self::STAT_CACHE) {
            $key = 'order_report_' . user()->tenant_id . '_' . $city_id . '_' . $first_date . '_' . $last_date;
            if ($tenantCompanyIds) {
                $key .= '_' . implode('_', $tenantCompanyIds);
            }
            if (!empty($arPositionId)) {
                $key .= '_' . implode('_', $arPositionId);
            }

            return CacheHelper::getFromCache($key,
                function () use ($city_id, $first_date, $last_date, $arPositionId, $tenantCompanyIds
            ) {
                return self::getStatData($city_id, $first_date, $last_date, $arPositionId, $tenantCompanyIds);
            });
        }

        return self::getStatData($city_id, $first_date, $last_date, $arPositionId, $tenantCompanyIds);
    }

    /**
     * Получение готового результата статистики.
     * @param integer $city_id
     * @param integer $first_date
     * @param integer $last_date
     * @param null|array $arPositionId Position list
     * @param null|integer[] $tenantCompanyIds
     * @return array
     */
    private static function getStatData($city_id, $first_date, $last_date, $arPositionId = null, $tenantCompanyIds = null)
    {
        if (is_array($arPositionId)) {
            $arPositionId = array_map(function ($value) {
                return (string)$value;
            }, $arPositionId);
        }

        if (is_array($tenantCompanyIds)) {
            $tenantCompanyIds = array_map(function ($value) {
                return (string)$value;
            }, $tenantCompanyIds);
        }

        $cityTimeoffset = City::getTimeOffset($city_id);

        $documents = self::find()
            ->where(['city_id' => (string)$city_id, 'tenant_id' => (string)user()->tenant_id])
            ->andWhere(['between', 'timestamp', $first_date + $cityTimeoffset, $last_date + $cityTimeoffset])
            ->andFilterWhere([
                'position_id' => $arPositionId,
                'tenant_company_id' => $tenantCompanyIds,
            ])
            ->asArray()
            ->select(['statistics', 'currency_id'])
            ->all();

        return !empty($documents) ? self::calculate($documents) : [];
    }

    /**
     * Суммирование всех документов.
     * @param array $documents Массив документов текущей модели
     * @return array
     */
    private static function calculate($documents)
    {
        $statistics = [];
        $parkingList = [];
        $tariffList = [];
        $arSameGroup = ['received', 'pre_order'];
        $completedCount = [];

        foreach ($documents as $document) {

            if (!isset($completedCount[$document['currency_id']])) {
                $completedCount[$document['currency_id']] = 0;
            }

            $completedCount[$document['currency_id']] += $document['statistics']['completed']['quantity'];

            //ПРИНЯТО и ПРЕДВАРИТЕЛЬНЫЕ
            foreach ($arSameGroup as $group) {

                if (!isset($statistics[$group])) {
                    $statistics[$group] = [
                        'quantity' => 0,
                        'device'   => [
                            'IOS'        => [],
                            'ANDROID'    => [],
                            'DISPATCHER' => 0,
                            'WORKER'     => 0,
                            'WEB'        => 0,
                            'CABINET'    => 0,
                        ],
                    ];
                }

                $statistics[$group]['quantity'] += $document['statistics'][$group]['quantity'];

                // Devices
                $deviceStatistics = $document['statistics'][$group]['device'];

                if (is_array($deviceStatistics['IOS'])) {
                    foreach ($deviceStatistics['IOS'] as $appId => $count) {
                        if (!isset($statistics[$group]['device']['IOS'][$appId])) {
                            $statistics[$group]['device']['IOS'][$appId] = 0;
                        }
                        $statistics[$group]['device']['IOS'][$appId] += $count;
                    }
                }

                if (is_array($deviceStatistics['ANDROID'])) {
                    foreach ($deviceStatistics['ANDROID'] as $appId => $count) {
                        if (!isset($statistics[$group]['device']['ANDROID'][$appId])) {
                            $statistics[$group]['device']['ANDROID'][$appId] = 0;
                        }
                        $statistics[$group]['device']['ANDROID'][$appId] += $count;
                    }
                }

                $statistics[$group]['device']['DISPATCHER'] += $deviceStatistics['DISPATCHER'];
                $statistics[$group]['device']['WORKER'] += $deviceStatistics['WORKER'];
                $statistics[$group]['device']['WEB'] += $deviceStatistics['WEB'];

                if (isset($deviceStatistics['CABINET'])) {
                    $statistics[$group]['device']['CABINET'] += $deviceStatistics['CABINET'];
                }

                // Detail information
                if (!isset($statistics[$group]['detail'])) {
                    $statistics[$group]['detail'] = [];
                }
                foreach ($document['statistics'][$group]['detail'] as $parking_id => $val) {
                    //Формируем список парковок для того, чтобы потом одним запросом определить их название.
                    if (!in_array($parking_id, $parkingList)) {
                        array_push($parkingList, $parking_id);
                    }

                    if (!isset($statistics[$group]['detail'][$parking_id])) {
                        $statistics[$group]['detail'][$parking_id] = [
                            'quantity'   => 0,
                            'IOS'        => [],
                            'ANDROID'    => [],
                            'DISPATCHER' => 0,
                            'WORKER'     => 0,
                            'WEB'        => 0,
                            'CABINET'    => 0,
                        ];
                    }

                    //Детально - количество
                    $statistics[$group]['detail'][$parking_id]['quantity'] += $val['quantity'];
                    //Детально - устройство
                    if (isset($val['IOS']) && is_array($val['IOS'])) {
                        foreach ($val['IOS'] as $appId => $count) {
                            if (!isset($statistics[$group]['detail'][$parking_id]['IOS'][$appId])) {
                                $statistics[$group]['detail'][$parking_id]['IOS'][$appId] = 0;
                            }
                            $statistics[$group]['detail'][$parking_id]['IOS'][$appId] += $count;
                        }
                    }

                    if (isset($val['ANDROID']) && is_array($val['ANDROID'])) {
                        foreach ($val['ANDROID'] as $appId => $count) {
                            if (!isset($statistics[$group]['detail'][$parking_id]['ANDROID'][$appId])) {
                                $statistics[$group]['detail'][$parking_id]['ANDROID'][$appId] = 0;
                            }
                            $statistics[$group]['detail'][$parking_id]['ANDROID'][$appId] += $count;
                        }
                    }

                    if (isset($val['DISPATCHER'])) {
                        $statistics[$group]['detail'][$parking_id]['DISPATCHER'] += $val['DISPATCHER'];
                    }
                    if (isset($val['WORKER'])) {
                        $statistics[$group]['detail'][$parking_id]['WORKER'] += $val['WORKER'];
                    }
                    if (isset($val['WEB'])) {
                        $statistics[$group]['detail'][$parking_id]['WEB'] += $val['WEB'];
                    }
                    if (isset($val['CABINET'])) {
                        $statistics[$group]['detail'][$parking_id]['CABINET'] += $val['CABINET'];
                    }
                }
            }
            //-----------------------------------------------------------------------------

            //ВЫПОЛНЕНО

            if (!isset($statistics['completed'])) {
                $statistics['completed'] = [
                    'quantity' => 0,
                    'sum'      => [],
                    'device'   => [
                        'IOS'        => [],
                        'ANDROID'    => [],
                        'DISPATCHER' => 0,
                        'WORKER'     => 0,
                        'WEB'        => 0,
                        'CABINET'    => 0,
                    ],
                    'detail'   => [
                        'tariffs'  => [],
                        'payment'  => [],
                        'averages' => [
                            'pick_up_sum_time' => 0,
                        ],
                    ],
                ];
            }

            //Количество
            $statistics['completed']['quantity'] += $document['statistics']['completed']['quantity'];
            //Сумма
            if (!isset($statistics['completed']['sum'][$document['currency_id']])) {
                $statistics['completed']['sum'][$document['currency_id']] = 0;
            }
            $statistics['completed']['sum'][$document['currency_id']] += $document['statistics']['completed']['sum'];
            //Устройство
            $deviceStatistics = $document['statistics']['completed']['device'];

            if (is_array($deviceStatistics['IOS'])) {
                foreach ($deviceStatistics['IOS'] as $appId => $count) {
                    if (!isset($statistics['completed']['device']['IOS'][$appId])) {
                        $statistics['completed']['device']['IOS'][$appId] = 0;
                    }
                    $statistics['completed']['device']['IOS'][$appId] += $count;
                }
            }

            if (is_array($deviceStatistics['ANDROID'])) {
                foreach ($deviceStatistics['ANDROID'] as $appId => $count) {
                    if (!isset($statistics['completed']['device']['ANDROID'][$appId])) {
                        $statistics['completed']['device']['ANDROID'][$appId] = 0;
                    }
                    $statistics['completed']['device']['ANDROID'][$appId] += $count;
                }
            }

            $statistics['completed']['device']['DISPATCHER'] += $deviceStatistics['DISPATCHER'];
            $statistics['completed']['device']['WORKER'] += $deviceStatistics['WORKER'];
            $statistics['completed']['device']['WEB'] += $deviceStatistics['WEB'];
            if (isset($deviceStatistics['CABINET'])) {
                $statistics['completed']['device']['CABINET'] += $deviceStatistics['CABINET'];
            }

            //Водители
            $statistics['completed']['workers'] = $document['statistics']['completed']['workers']['cnt'];
            //-------------------------------------------------

            // Detail information of tariffs
            foreach ($document['statistics']['completed']['detail']['tariffs'] as $tariff_id => $value) {
                //Формируем список тарифов для того, чтобы потом одним запросом определить их название.
                if (!in_array($tariff_id, $tariffList)) {
                    array_push($tariffList, $tariff_id);
                }

                if (!isset($statistics['completed']['detail']['tariffs'][$tariff_id])) {
                    $statistics['completed']['detail']['tariffs'][$tariff_id] = [
                        'quantity'   => 0,
                        'sum'        => [],
                        'IOS'        => [],
                        'ANDROID'    => [],
                        'DISPATCHER' => 0,
                        'WORKER'     => 0,
                        'WEB'        => 0,
                        'CABINET'    => 0,
                        'workers'    => [
                            'cnt' => 0,
                        ],
                    ];
                }
                //Количество
                $statistics['completed']['detail']['tariffs'][$tariff_id]['quantity'] += $value['quantity'];
                //Cумма
                if (!isset($statistics['completed']['detail']['tariffs'][$tariff_id]['sum'][$document['currency_id']])) {
                    $statistics['completed']['detail']['tariffs'][$tariff_id]['sum'][$document['currency_id']] = 0;
                }

                if (isset($value['price'])) {
                    $statistics['completed']['detail']['tariffs'][$tariff_id]['sum'][$document['currency_id']] += $value['price'];
                }
                //Устройство
                if (isset($value['IOS']) && is_array($value['IOS'])) {
                    foreach ($value['IOS'] as $appId => $count) {
                        if (!isset($statistics['completed']['detail']['tariffs'][$tariff_id]['IOS'][$appId])) {
                            $statistics['completed']['detail']['tariffs'][$tariff_id]['IOS'][$appId] = 0;
                        }
                        $statistics['completed']['detail']['tariffs'][$tariff_id]['IOS'][$appId] += $count;
                    }
                }

                if (isset($value['ANDROID']) && is_array($value['ANDROID'])) {
                    foreach ($value['ANDROID'] as $appId => $count) {
                        if (!isset($statistics['completed']['detail']['tariffs'][$tariff_id]['ANDROID'][$appId])) {
                            $statistics['completed']['detail']['tariffs'][$tariff_id]['ANDROID'][$appId] = 0;
                        }
                        $statistics['completed']['detail']['tariffs'][$tariff_id]['ANDROID'][$appId] += $count;
                    }
                }

                if (isset($value['DISPATCHER'])) {
                    $statistics['completed']['detail']['tariffs'][$tariff_id]['DISPATCHER'] += $value['DISPATCHER'];
                }
                if (isset($value['WORKER'])) {
                    $statistics['completed']['detail']['tariffs'][$tariff_id]['WORKER'] += $value['WORKER'];
                }
                if (isset($value['WEB'])) {
                    $statistics['completed']['detail']['tariffs'][$tariff_id]['WEB'] += $value['WEB'];
                }
                if (isset($value['CABINET'])) {
                    $statistics['completed']['detail']['tariffs'][$tariff_id]['CABINET'] += $value['CABINET'];
                }
                //Водители
                if (isset($value['workers']['cnt'])) {
                    $statistics['completed']['detail']['tariffs'][$tariff_id]['workers']['cnt'] += $value['workers']['cnt'];
                }
            }

            //--------------------------------------------------

            // Detail information of payments
            foreach ($document['statistics']['completed']['detail']['payment'] as $payment => $value) {
                if (!isset($statistics['completed']['detail']['payment'][$payment])) {
                    $statistics['completed']['detail']['payment'][$payment] = [
                        'quantity'   => 0,
                        'sum'        => [],
                        'IOS'        => [],
                        'ANDROID'    => [],
                        'DISPATCHER' => 0,
                        'WORKER'     => 0,
                        'WEB'        => 0,
                        'CABINET'    => 0,
                        'workers'    => [
                            'cnt' => 0,
                        ],
                    ];
                }
                //Количество
                $statistics['completed']['detail']['payment'][$payment]['quantity'] += $value['quantity'];
                //Cумма
                if (!isset($statistics['completed']['detail']['payment'][$payment]['sum'][$document['currency_id']])) {
                    $statistics['completed']['detail']['payment'][$payment]['sum'][$document['currency_id']] = 0;
                }
                $statistics['completed']['detail']['payment'][$payment]['sum'][$document['currency_id']] += $value['sum'];
                //Устройство
                if (isset($value['IOS']) && is_array($value['IOS'])) {
                    foreach ($value['IOS'] as $appId => $count) {
                        if (!isset($statistics['completed']['detail']['payment'][$payment]['IOS'][$appId])) {
                            $statistics['completed']['detail']['payment'][$payment]['IOS'][$appId] = 0;
                        }
                        $statistics['completed']['detail']['payment'][$payment]['IOS'][$appId] += $count;
                    }
                }

                if (isset($value['ANDROID']) && is_array($value['ANDROID'])) {
                    foreach ($value['ANDROID'] as $appId => $count) {
                        if (!isset($statistics['completed']['detail']['payment'][$payment]['ANDROID'][$appId])) {
                            $statistics['completed']['detail']['payment'][$payment]['ANDROID'][$appId] = 0;
                        }
                        $statistics['completed']['detail']['payment'][$payment]['ANDROID'][$appId] += $count;
                    }
                }

                if (isset($value['DISPATCHER'])) {
                    $statistics['completed']['detail']['payment'][$payment]['DISPATCHER'] += $value['DISPATCHER'];
                }
                if (isset($value['WORKER'])) {
                    $statistics['completed']['detail']['payment'][$payment]['WORKER'] += $value['WORKER'];
                }
                if (isset($value['WEB'])) {
                    $statistics['completed']['detail']['payment'][$payment]['WEB'] += $value['WEB'];
                }
                if (isset($value['CABINET'])) {
                    $statistics['completed']['detail']['payment'][$payment]['CABINET'] += $value['CABINET'];
                }
                //Водители
                if (isset($value['workers']['cnt'])) {
                    $statistics['completed']['detail']['payment'][$payment]['workers']['cnt'] = $value['workers']['cnt'];
                }
            }
            //---------------------------------------------------

            //Детально - Средние показатели
            if (!isset($statistics['completed']['detail']['averages']['price_sum'][$document['currency_id']])) {
                $statistics['completed']['detail']['averages']['price_sum'][$document['currency_id']] = 0;
            }
            $statistics['completed']['detail']['averages']['price_sum'][$document['currency_id']] += $document['statistics']['completed']['detail']['averages']['price'];
            $statistics['completed']['detail']['averages']['pick_up_sum_time'] += $document['statistics']['completed']['detail']['averages']['pick_up_sum_time'];
            //-----------------------------------------------------------------------------

            //ВНИМАНИЕ И ОТМЕНЕННЫЕ
            if (!isset($statistics['rejected'])) {
                $statistics['rejected'] = [
                    'quantity' => 0,
                    'sum'      => [],
                    'device'   => [
                        'IOS'        => [],
                        'ANDROID'    => [],
                        'DISPATCHER' => 0,
                        'WORKER'     => 0,
                        'WEB'        => 0,
                        'CABINET'    => 0,
                    ],
                    'detail'   => [
                        'reasons'      => [
                            'rejected' => [],
                            'warning'  => [],
                        ],
                    ],
                ];
            }
            //Количество
            $statistics['rejected']['quantity'] += $document['statistics']['rejected']['quantity'];
            //Cумма
            if (!isset($statistics['rejected']['sum'][$document['currency_id']])) {
                $statistics['rejected']['sum'][$document['currency_id']] = 0;
            }
            $statistics['rejected']['sum'][$document['currency_id']] += $document['statistics']['rejected']['sum'];
            //Устройство
            $deviceStatistics = $document['statistics']['rejected']['device'];

            if (is_array($deviceStatistics['IOS'])) {
                foreach ($deviceStatistics['IOS'] as $appId => $count) {
                    if (!isset($statistics['rejected']['device']['IOS'][$appId])) {
                        $statistics['rejected']['device']['IOS'][$appId] = 0;
                    }
                    $statistics['rejected']['device']['IOS'][$appId] += $count;
                }
            }

            if (is_array($deviceStatistics['ANDROID'])) {
                foreach ($deviceStatistics['ANDROID'] as $appId => $count) {
                    if (!isset($statistics['rejected']['device']['ANDROID'][$appId])) {
                        $statistics['rejected']['device']['ANDROID'][$appId] = 0;
                    }
                    $statistics['rejected']['device']['ANDROID'][$appId] += $count;
                }
            }

            $statistics['rejected']['device']['DISPATCHER'] += $deviceStatistics['DISPATCHER'];
            $statistics['rejected']['device']['WORKER'] += $deviceStatistics['WORKER'];
            $statistics['rejected']['device']['WEB'] += $deviceStatistics['WEB'];
            if (isset($deviceStatistics['CABINET'])) {
                $statistics['rejected']['device']['CABINET'] += $deviceStatistics['CABINET'];
            }
            //Водители
            $statistics['rejected']['workers'] = $document['statistics']['rejected']['workers']['cnt'];
            //---------------------------------------------------------------------------

            //Detail information of list rejected reasons
            foreach ($document['statistics']['rejected']['detail']['reasons']['rejected'] as $reason => $val) {
                if ($reason == 'after') {
                    foreach ($val as $statusId => $afterReasonCnt) {
                        if (!isset($statistics['rejected']['detail']['reasons']['rejected'][$reason][$statusId])) {
                            $statistics['rejected']['detail']['reasons']['rejected'][$reason][$statusId] = 0;
                        }
                        $statistics['rejected']['detail']['reasons']['rejected'][$reason][$statusId] += $afterReasonCnt;
                    }
                } elseif (!isset($statistics['rejected']['detail']['reasons']['rejected'][$reason])) {
                    $statistics['rejected']['detail']['reasons']['rejected'][$reason] = 0;
                    $statistics['rejected']['detail']['reasons']['rejected'][$reason] += $val;
                }
            }

            // Detail information of warning reasons
            foreach ($document['statistics']['rejected']['detail']['reasons']['warning'] as $warning => $val) {
                if (!isset($statistics['rejected']['detail']['reasons']['warning'][$warning]['rejected'])) {
                    $statistics['rejected']['detail']['reasons']['warning'][$warning]['rejected'] = 0;
                }
                $statistics['rejected']['detail']['reasons']['warning'][$warning]['rejected'] += $val['rejected'];

                if (!isset($statistics['rejected']['detail']['reasons']['warning'][$warning]['cnt'])) {
                    $statistics['rejected']['detail']['reasons']['warning'][$warning]['cnt'] = 0;
                }
                $statistics['rejected']['detail']['reasons']['warning'][$warning]['cnt'] += $val['cnt'];
            }

            //Детально - Плохие отзывы
            if (!isset($statistics['bad_feedback'])) {
                $statistics['bad_feedback'] = [
                    'one' => 0,
                    'two' => 0,
                ];
            }

            $statistics['bad_feedback']['one'] += $document['statistics']['bad_feedback']['one'];
            $statistics['bad_feedback']['two'] += $document['statistics']['bad_feedback']['two'];
        }

        //ВЫЧИСЛЕНИЕ ПРОЦЕНТОВ

        //Принятые и предварительные
        $statistics['pre_order']['percent'] = $statistics['received']['quantity'] ? app()->formatter->format($statistics['pre_order']['quantity'] / $statistics['received']['quantity'],
            ['percent', self::PERCENT_DIGIT]) : app()->formatter->format(0, ['percent', self::PERCENT_DIGIT]);
        //Детально
        foreach ($arSameGroup as $group) {
            foreach ($statistics[$group]['detail'] as $parking_id => $val) {
                $statistics[$group]['detail'][$parking_id]['percent'] = $statistics[$group]['quantity'] ? app()->formatter->format($val['quantity'] / $statistics[$group]['quantity'],
                    ['percent', self::PERCENT_DIGIT]) : app()->formatter->format(0, ['percent', self::PERCENT_DIGIT]);
            }
        }
        //------------------------------------------------------------------------------------

        //Выполнено
        $statistics['completed']['percent'] = $statistics['received']['quantity'] ? app()->formatter->format($statistics['completed']['quantity'] / $statistics['received']['quantity'],
            ['percent', self::PERCENT_DIGIT]) : app()->formatter->format(0, ['percent', self::PERCENT_DIGIT]);
        //Детально - Тарифы
        foreach ($statistics['completed']['detail']['tariffs'] as $tariff_id => $val) {
            $statistics['completed']['detail']['tariffs'][$tariff_id]['percent'] = $statistics['completed']['quantity'] ? app()->formatter->format($val['quantity'] / $statistics['completed']['quantity'],
                ['percent', self::PERCENT_DIGIT]) : app()->formatter->format(0, ['percent', self::PERCENT_DIGIT]);
        }
        //Детально - Виды оплат
        foreach ($statistics['completed']['detail']['payment'] as $payment => $val) {
            $statistics['completed']['detail']['payment'][$payment]['percent'] = $statistics['completed']['quantity'] != 0 ? app()->formatter->format($val['quantity'] / $statistics['completed']['quantity'],
                ['percent', self::PERCENT_DIGIT]) : app()->formatter->format(0, ['percent', self::PERCENT_DIGIT]);
        }
        //------------------------------------------------------------------------------------

        //Отмененные
        $statistics['rejected']['percent'] = $statistics['received']['quantity'] ? app()->formatter->format($statistics['rejected']['quantity'] / $statistics['received']['quantity'],
            ['percent', self::PERCENT_DIGIT]) : app()->formatter->format(0, ['percent', self::PERCENT_DIGIT]);
        //------------------------------------------------------------------------------------

        //Вычисление средних показателей
        $statistics['completed']['detail']['averages']['pick_up'] = empty($statistics['completed']['quantity']) ?
            0 : round($statistics['completed']['detail']['averages']['pick_up_sum_time'] / $statistics['completed']['quantity']);
        if (!empty($completedCount)) {
            foreach ($completedCount as $currency_id => $count) {
                $statistics['completed']['detail']['averages']['price'][$currency_id]
                    = empty($count) ? 0 : round($statistics['completed']['sum'][$currency_id] / $count);
            }
        }
        //---------------------------------------------------------------------------------

        $tenant_id = user()->tenant_id;

        //Формирование карты парковок
        $arParkingObj = Parking::find()
            ->where(['parking_id' => $parkingList, 'tenant_id' => $tenant_id])
            ->select('parking_id, name')
            ->all();
        $statistics['parking_map'] = ArrayHelper::map($arParkingObj, 'parking_id', 'name');

        //Формирование карты тарифов
        $arTariffObj = TaxiTariff::find()
            ->where(['tenant_id' => $tenant_id, 'tariff_id' => $tariffList])
            ->joinWith('class')
            ->asArray()
            ->all();
        $statistics['tariff_map'] = ArrayHelper::map($arTariffObj, 'tariff_id', 'name');

        //Формирование карты статусов
        $statistics['status_map'] = ArrayHelper::map(OrderStatus::getStatusData(), 'status_id', function ($item) {
            return OrderStatusService::translate($item['status_id']);
        });

        return $statistics;
    }

    /**
     * Перевод группы статистики.
     * @param string $group
     * @return string
     */
    public static function getGroupName($group)
    {
        $arStatisticGroupName = [
            'received'  => t('reports', 'Received'),
            'pre_order' => t('reports', 'Pre order'),
            'completed' => t('reports', 'Completed'),
            'rejected'  => t('reports', 'Warning and rejected'),
        ];

        return getValue($arStatisticGroupName[$group], 'None');
    }
}
