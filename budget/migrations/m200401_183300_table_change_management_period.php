<?php

use yii\db\Migration;

class m200401_183300_table_change_management_period extends Migration
{
    private $table = 'budget_management_periods';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn($this->table, 'code');
        $this->addColumn($this->table, 'date', 'datetime');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn($this->table, 'date');
        $this->addColumn($this->table, 'code', $this->string(255));
    }
}
