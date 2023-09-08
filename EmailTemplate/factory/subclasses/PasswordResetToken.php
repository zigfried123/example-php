<?php
/**
 * Created by PhpStorm.
 * User: zigfried123
 * Date: 17.04.2018
 * Time: 13:01
 */

namespace app\modules\v1\models\factory\subclasses;

use app\modules\v1\models\factory\subclasses\traits\TemplatesTrait;
use \Yii;

use yii\helpers\Html;

class PasswordResetToken
{
    use TemplatesTrait;

    private $name;
    private $url;
    private $footer;
    private $lang;

    protected function getContent()
    {
        $text = 'Dear {name},<br>';
        $text .= 'You recently have requested a password reset. Please use the link below to change your password.<br>';
        $text .= '<a href="{url}">{url}</a><br>';
        $text .= '{footer}';

        return Yii::t('email', $text, [
            'name'   => Html::encode($this->name),
            'url'    => Html::encode($this->url),
            'footer' => Html::encode($this->footer),
        ], $this->lang);

    }
}