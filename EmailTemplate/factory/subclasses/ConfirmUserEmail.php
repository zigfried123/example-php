<?php
/**
 * Created by PhpStorm.
 * User: zigfried123
 * Date: 17.04.2018
 * Time: 8:36
 */

namespace app\modules\v1\models\factory\subclasses;

use app\modules\v1\models\factory\subclasses\traits\TemplatesTrait;
use yii\helpers\Html;

class ConfirmUserEmail
{
    use TemplatesTrait;

    private $name;
    private $url;
    private $password;
    private $lang;

    protected function getContent()
    {
        $text = 'Dear {name},<br>';
        $text .= 'Thank you for registering.<br>';
        $text .= 'Below you will find your activation link that you can use to activate your account. Please click on the Activation Link {link}<br>';
        $text .= 'Then, you will be able to log in and begin using your account.<br>';
        $text .= 'Your password: {password}<br>';

        return \Yii::t('email',
            $text,
            [
                'name'     => Html::encode($this->name),
                'link'     => Html::a($this->url, $this->url),
                'password' => isset($this->password) ? $this->password : null,
            ], $this->lang);
    }
}