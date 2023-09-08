<?php

class Payment
{

    private function getItems($totalAmount)
    {
        $items = [];

        if (!empty($this->cart->articles)) {
            $ids = Article::findAll(array_column($this->cart->articles, 'id'));
        }

        foreach ($this->cart->articles as $key => $article) {
            $items[] = [
                //'description' => $ids[$key]->description != '' ? $ids[$key]->description : 'Отсутствует',
                'description' => $article->name,
                'quantity' => number_format($article->quantity, 2, '.', ''),
                'amount' => [
                    'value' => number_format($article->amount, 2, '.', ''),
                    'currency' => $this->currency
                ],
                'vat_code' => '1', // они на упрощенке
                'payment_mode' => 'full_prepayment',
                'payment_subject' => 'commodity'
            ];
        }

        $itemsAmount = $this->getItemsAmount($items);

        $shippingAmount = $totalAmount - $itemsAmount;

        $items[] = $this->getShippingData($shippingAmount);

        return $items;
    }

    private function getItemsAmount($items)
    {
        $amount = array_sum(array_column(array_column($items, 'amount'), 'value'));

        return $amount;
    }

    private function getShippingData($shippingAmount)
    {
        $data = [
            'description' => 'Доставка',
            'quantity' => number_format(1, 2, '.', ''),
            'amount' => [
                'value' => number_format($shippingAmount, 2, '.', ''),
                'currency' => $this->currency
            ],
            'vat_code' => '1', // они на упрощенке
            'payment_mode' => 'full_prepayment',
            'payment_subject' => 'commodity'
        ];

        return $data;
    }

    private function getPhone($userId)
    {
        if ($userId == 'null') {
            $phone = $this->cart->newAgentPhone;
        } else {
            if (is_object(\Yii::$app->user->getIdentity())) {
                $user = \Yii::$app->user->getIdentity()->client;
                $phone = $user->Phone;
            }
        }

        $phone = preg_replace('/[^0-9]/u', '', $phone);

        return $phone;
    }

    private function getEmail($userId)
    {
        if ($userId == 'null') {
            $email = $this->cart->newAgentEmail;
        } else {
            if (is_object(\Yii::$app->user->getIdentity())) {
                $user = \Yii::$app->user->getIdentity()->client;
                $email = $user->Email;
            }
        }

        return $email;
    }

    private function getReturnUrl($orderId)
    {
        return Url::to("/payment/to-return-url?orderId=$orderId", true);
    }

    private function getConfirmationUrl()
    {
        return $this->confirmationUrl;
    }

    public function createPaymentRequest($amount, $orderId, $userId)
    {
        $client = new Client();

        $client->setAuth($this->shopId, $this->secret);

        $amount = number_format($amount, 2, '.', '');

        try {
            $params = [
                'amount' => [
                    'value' => $amount,
                    'currency' => $this->currency,
                ],
                'metadata' => ['order_id' => $orderId, 'user_id' => $userId],
                'confirmation' => [
                    'type' => $this->type,
                    'return_url' => $this->getReturnUrl($orderId),
                    //'confirmation_url' => $this->confirmationUrl
                ],
                'receipt' => [
                    'phone' => $this->getPhone($userId),
                    'items' => $this->getItems($amount)
                ],
                'capture' => $this->capture,
                'description' => $this->description
            ];

            \Yii::info('Params to yandex kassa: ' . json_encode($params, JSON_UNESCAPED_UNICODE), 'payment');

            $payment = $client->createPayment(
                $params,
                uniqid('', true)
            );
        } catch (\Exception $e) {
            //var_dump($e->getTraceAsString());
            throw new \Exception($e->getMessage(), $e->getCode());
        }

        return $payment;
    }

}