<?php
/**
 * Created by PhpStorm.
 * User: zigfried123
 * Date: 18.04.2018
 * Time: 13:34
 */

namespace app\modules\v1\models\factory\subclasses;

use \Yii;
use app\modules\v1\models\factory\subclasses\traits\TemplatesTrait;
use yii\helpers\Html;

class ChatMessage
{
    use TemplatesTrait;

    private $name;
    private $lang;

    protected function getContent()
    {
        $text = 'Dear {name},<br>';
        $text .= 'Received a chat message';

        return Yii::t('email', $text, [
            'name'   => Html::encode($this->name),
        ], $this->lang);

    }
}