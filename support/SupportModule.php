<?php

namespace app\modules\support;

use app\modules\support\controllers\ApiController;
use yii\base\Module as BaseModule;

class SupportModule extends BaseModule
{
    public $controllerNamespace = 'app\modules\support\commands';

    public $controllerMap = [
        'api' => ApiController::class,
    ];

    public function init()
    {
        parent::init();
    }
}
