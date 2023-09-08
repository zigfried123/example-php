<?php

use yii\db\Migration;

class m200331_123000_table_change_budget_payment_sys extends Migration
{
    private $table = 'budget_payment_systems';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn($this->table, 'code');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn($this->table, 'code', $this->string(255));
    }
}
