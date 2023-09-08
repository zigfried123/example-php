<?php


namespace app\modules\reports\models\orders;


use app\modules\order\models\Order;
use app\modules\order\models\OrderStatus;
use common\modules\city\models\City;
use common\services\OrderStatusService;
use frontend\modules\car\models\CarClass;
use frontend\modules\companies\components\repositories\TenantCompanyRepository;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

class OrderSearch extends BaseOrderSearch
{
    const PAGE_SIZE = 50;

    public $payment;
    public $order_number;
    public $callsign;
    public $class_id;
    public $status_id;
    public $device;
    public $order_id;
    public $tenant_company_id;
    public $tenantCompanyIds;


    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            ['device', 'in', 'range' => array_keys($this->getDeviceMap()), 'allowArray' => true],
            ['payment', 'in', 'range' => array_keys($this->getPaymentMap()), 'allowArray' => true],
            ['class_id', 'in', 'range' => array_keys($this->getCarClassMap()), 'allowArray' => true],
            ['status_id', 'in', 'range' => array_keys($this->getStatusMap()), 'allowArray' => true],
            [['order_number', 'callsign', 'tenant_company_id'], 'integer'],
            ['tenantCompanyIds', 'each', 'rule' => ['integer']]
        ]);
    }

    public function search($usePagination = true)
    {

        $query = Order::find()
            ->alias('o')
            ->groupBy('order_id')
            ->joinWith([
                'client cl',
                'worker w',
                'car car',
                'status st',
                'detailCost dc',
                'clientReview rvw',
                'client.clientPhones cp',
                'company c',
            ]);

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'sort'       => [
                'defaultOrder' => [
                    'order_id' => SORT_DESC,
                ],
                'attributes'   => [],
            ],
            'pagination' => $usePagination ? ['pageSize' => self::PAGE_SIZE] : false,
        ]);


        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->filterWhere([
            'o.tenant_company_id' => $this->tenant_company_id,
        ]);
        $query->orFilterWhere([
            'w.tenant_company_id' => $this->tenant_company_id,
        ]);


        $query->andFilterWhere([
            'o.city_id'           => $this->getCityId(),
            'o.payment'           => $this->payment,
            'o.order_number'      => $this->order_number,
            'car.class_id'        => $this->class_id,
            'w.callsign'          => $this->callsign,
            'o.status_id'         => $this->status_id,
            'o.device'            => $this->device,
            'o.position_id'       => $this->getPositionList(),
        ]);

        $query
            ->andWhere([
                'o.tenant_id' => user()->tenant_id,
                'o.city_id'   => $this->access_city_list,
            ]);

        $timeOffset = City::getTimeOffset($this->getCityId());

        $query->andWhere([
            'between',
            'o.create_time',
            $this->first_date - $timeOffset,
            $this->second_date - $timeOffset,
        ]);

        if (isset($this->order_id)) {
            $query->where(['o.order_id' => $this->order_id]);
        }

        $query->andFilterWhere(['o.tenant_company_id' => $this->tenantCompanyIds]);

        return $dataProvider;
    }

    /**
     * @return array
     */
    public function getDeviceMap()
    {
        return [
            Order::DEVICE_0       => t('order', 'Dispatcher'),
            Order::DEVICE_CABINET => t('order', 'Cabinet'),
            Order::DEVICE_WEB     => t('order', 'Web site'),
            Order::DEVICE_WORKER  => t('order', 'Border'),
            Order::DEVICE_ANDROID => 'Android',
            Order::DEVICE_IOS     => 'IOS',
            Order::DEVICE_YANDEX  => 'Yandex',
        ];
    }

    /**
     * @return array
     */
    public function getPaymentMap()
    {
        return [
            Order::PAYMENT_CASH   => t('order', 'Cash'),
            Order::PAYMENT_CARD   => t('order', 'Bank card'),
            Order::PAYMENT_PERSON => t('order', 'Personal account'),
            Order::PAYMENT_CORP   => t('order', 'Corporate balance'),
        ];
    }

    /**
     * @return array
     */
    public function getStatusMap()
    {
        $finishedStatusIds = OrderStatus::getFinishedStatusId();

        $statuses = OrderStatus::find()
            ->where(['status_id' => $finishedStatusIds])
            ->select(['status_id', 'name'])
            ->all();

        return ArrayHelper::map($statuses, 'status_id', function ($item) {
            $statusName = OrderStatusService::translate($item['status_id']);

            if ($item['status_id'] == OrderStatus::STATUS_NO_CARS_BY_TIMER) {
                $statusName .= ' (' . t('reports', 'by timer') . ')';
            }

            return $statusName;
        });
    }

    public function getTenantCompanies()
    {
        return TenantCompanyRepository::selfCreate()
            ->getForForm(['tenant_id' => user()->tenant_id]);
    }

    /**
     * @return array
     */
    public function getCarClassMap()
    {
        return CarClass::getClasses(true);
    }

}