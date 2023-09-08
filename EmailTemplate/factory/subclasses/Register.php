<?php
/**
 * Created by PhpStorm.
 * User: zigfried123
 * Date: 20.04.2018
 * Time: 17:08
 */

namespace app\modules\v1\models\factory\subclasses;


use app\modules\v1\models\factory\subclasses\traits\TemplatesTrait;
use yii\helpers\Html;

class Register
{
    use TemplatesTrait;

    private $user;
    private $url;
    private $login;
    private $lang;
    private $password;

    protected function getContent()
    {
        $text = 'Hello {user},<br>';
        $text .= 'Thank you for registration at the Gootax! Please save your data to login.<br>';
        $text .= '<b>Link of your system</b><br>';
        $text .= '<a href={url} target="_blank">{url}</a><br>';
        $text .= '<b>Login:</b><br><br>';
        $text .= '{login}';
        $text .= '<b>Password:</b><br><br>';
        $text .= '{password}';

        return \Yii::t('email', $text, [
            'user'   => Html::encode($this->user),
            'url'    => Html::encode($this->url),
            'login' => Html::encode($this->login),
            'password' => Html::encode($this->password),
        ], $this->lang);

    }

}