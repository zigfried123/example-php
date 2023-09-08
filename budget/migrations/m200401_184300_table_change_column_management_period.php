<?php

use yii\db\Migration;

class m200401_184300_table_change_column_management_period extends Migration
{
    private $table = 'budget_management_periods';
    private $column = 'date';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn($this->table, $this->column, 'VARCHAR(50)');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn($this->table, $this->column, 'datetime');
    }
}
