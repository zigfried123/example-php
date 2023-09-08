<?php

namespace app\models\request;

use app\models\traits\Singleton;
use yii\base\Model;

/**
 * Class VendorRequest
 * @package app\models\request
 * @author Maxim Protodyakonov <zigfried123@mail.ru>
 */
abstract class VendorRequest extends Model
{
    use Singleton;

    abstract public function execute();
}