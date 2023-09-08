<?php

use yii\db\Migration;

class m200326_120000_table_planfix_managment_period extends Migration
{
    private $table = 'budget_management_periods';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->table, [
            'id' => $this->primaryKey(),
            'code' => $this->string(255),
            'name' => $this->string(255),
            'is_deleted' => $this->boolean(),
            'created' => $this->dateTime(),
            'updated' => $this->dateTime(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->table);
    }
}
