<?php
/**
 * Created by PhpStorm.
 * User: zigfried123
 * Date: 20.04.2018
 * Time: 17:06
 */

namespace app\modules\v1\models\factory\subclasses;


use app\modules\v1\models\factory\subclasses\traits\TemplatesTrait;
use yii\helpers\Html;

class PaymentNotice
{
    use TemplatesTrait;

    private $lang;
    private $user;
    private $money;

    protected function getContent()
    {
        $text = 'Hello {user}!<br>';
        $text .= 'We received your payment of {money}.<br>';
        $text .= 'Thank you for your choice.';

        return \Yii::t('email', $text, [
            'user'   => Html::encode($this->user),
            'money'    => Html::encode($this->money),
        ], $this->lang);

    }
}