<?php

namespace app\modules\support\services;

use Yii;
use yii\db\Connection;
use yii\db\Transaction;

class BaseService
{

    /** @var Connection */
    protected $dbLocal;


    /** @var Transaction */
    protected $transaction;

    public function __construct()
    {

        $this->dbLocal = Yii::$app->dbPlanfixSync;

    }

    public function transactionStart()
    {
        $this->transaction = $this->dbLocal->beginTransaction();
    }

    public function transactionCommit()
    {
        $this->transaction->commit();
    }

    public function transactionRollback()
    {
        $this->transaction->rollBack();
    }
}
