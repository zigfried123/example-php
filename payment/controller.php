<?php

class DefaultController extends Controller
{
    public $enableCsrfValidation = false;

    private $_paymentStatus;
    private $_payment;
    private $_paymentDb;
    private $_paymentClass;

    public function init()
    {
        $this->_payment = new Payment();
        $this->_paymentDb = new \app\models\Payment();
        $this->_paymentClass = get_class($this->_payment);
        $this->_paymentStatus = new PaymentStatus();
    }

    public function actionPurchase()
    {
        $request = \Yii::$app->request->get();


        if (!isset($request['amount'])) {
            throw new Exception('Parameter is wrong');
        }

        if (!isset($request['order_id'])) {
            throw new Exception('Parameter is wrong');
        }

        \Yii::$app->cart->setOrderId($request['order_id']);

        try {
            $response = $this->_payment->createPaymentRequest($request['amount'], $request['order_id'], $request['user_id']);

            $statusModel = $this->getStatusModel($response);

            $this->savePayment($statusModel);

            $this->redirectToConfirmation($response->confirmation->confirmation_url);
        } catch (\Exception $e) {
            $handler = new CodeExceptionHandler();
            $handler->handle($e);
            $message = $handler->getMessage();

            $messageForAdmin = $handler->getMessageForAdmin();

            \Yii::info($messageForAdmin, 'payment');

            throw new Exception($message);
        }
    }

    public function actionPushNotification()
    {
        $response = \Yii::$app->request->post();

        \Yii::info('yandex response: ' . json_encode($response, JSON_UNESCAPED_UNICODE), 'payment');

        $statusModel = $this->getStatusModel($response);

        \Yii::info('cancel message: ' . json_encode($statusModel->getMessage(), JSON_UNESCAPED_UNICODE), 'payment');

        $this->savePayment($statusModel);


        if ($statusModel->getStatus() == PaymentStatus::SUCCEEDED_STATUS) {
            $orderId = $statusModel->getOrderId();
            $paymentId = \app\models\Payment::getLastPaymentByOrderId($orderId)->payment_id;
            \Yii::info('SUCCESS PARAMS: $orderId=' . $orderId . ', $paymentId=' . $paymentId, 'payment');
            try {
                $params = new DocumentErp();
                $params->SaleDocId = $orderId;
                $params->PaymentId = $paymentId;
                \Yii::$app->ultimateClient->createDocumentErp($params);
            } catch (\Exception $e) {
                \Yii::info('ERROR CreateAquiringPayment:' . $e->getMessage() . "\n" . json_encode($params, JSON_UNESCAPED_UNICODE), 'payment');
            }
        }

        return true;
    }

    public function actionToReturnUrl()
    {
        $orderId = \Yii::$app->request->get('orderId');

        $paymentId = \app\models\Payment::getLastPaymentByOrderId($orderId)->payment_id;

        $shopId = $this->_payment->shopId;
        $secret = $this->_payment->secret;

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => "https://payment.yandex.net/api/v3/payments/$paymentId",
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_RETURNTRANSFER => true,
            //CURLOPT_HEADER => true,
            //CURLOPT_NOBODY => true,
            CURLOPT_HTTPAUTH => CURLAUTH_ANY,
            CURLOPT_USERPWD => "$shopId:$secret"
        ]);
        $r = curl_exec($ch);

        $r = json_decode($r);

        curl_close($ch);


        \Yii::info('yandex return data: ' . json_encode($r, JSON_UNESCAPED_UNICODE), 'payment');


        //$status = ArrayHelper::getValue(json_decode($r), 'status');


        //\Yii::info('yandex return status: ' . $status, 'payment');


        if ($r->status == PaymentStatus::SUCCEEDED_STATUS) {
            $url = Url::to('/order/success?orderId=' . $orderId, true);
        } elseif ($r->status == PaymentStatus::CANCELED_STATUS) {
            $url = Url::to('/order/cancel?orderId=' . $orderId, true);
        } elseif ($r->status == PaymentStatus::PENDING_STATUS) {
            $url = Url::to('/order/pending?orderId=' . $orderId, true);
        }

        \Yii::$app->cart->cleanup();
        $this->redirect($url);
    }

    public function actionGetLastOrderData()
    {
        $orderId = \Yii::$app->cart->getOrderId();

        $articles = \Yii::$app->cart->getArticles();

        $deliveryCost = \Yii::$app->cart->getYandexDeliveryCost();

        return json_encode(['id' => $orderId, 'articles' => $articles, 'deliveryCost' => $deliveryCost]);
    }

    /**
     * ToDo убрать
     */
    public function actionCreateDocumentErp($orderId = null)
    {
        $post = \Yii::$app->request->post();

        $orderId = $post ? $post['order_id'] : $orderId;

        $params = new DocumentErp();
        $params->SaleDocId = $orderId;
        return \Yii::$app->ultimateClient->createDocumentErp($params);
    }

    public function actionGetCancelData()
    {
        $orderId = \Yii::$app->cart->getOrderId();

        $payment = \app\models\Payment::getLastPaymentByOrderId($orderId);
        return json_encode(['orderId' => $orderId, 'message' => $payment->message]);
    }

    private function getStatusModel($response): ResponseStatusAbstract
    {
        $handler = new ResponseHandler($response);

        $handler->handleStatus();

        /** @var ResponseStatusAbstract $model */
        $statusModel = $handler->getModel();

        return $statusModel;
    }

    private function redirectToConfirmation($url)
    {
        if (isset($url)) {
            return $this->redirect($url);
        }
    }

    /** @var ResponseStatusAbstract $statusModel */
    private function savePayment($statusModel)
    {
        /** @var \app\models\Payment $model */
        $model = $this->_paymentDb;

        $userId = $statusModel->getUserId();
        $orderId = $statusModel->getOrderId();
        $id = $statusModel->getPaymentId();


        if (!$model->isModelExists([
            'order_id' => $orderId,
            'status' => PaymentStatus::SUCCEEDED_STATUS,
        ])) {
            $data = [
                'order_id' => $orderId,
                'status' => PaymentStatus::getStatusIdByName($statusModel->getStatus()),
                'payment_id' => $id,
                'message' => $statusModel->getMessage()
            ];
            if ($userId != 'null') {
                $data['user_id'] = $userId;
            }
            $model->createModel($data);
        }

        \Yii::info('errors: ' . json_encode($model->errors, JSON_UNESCAPED_UNICODE), 'payment');

        return true;
    }
}
