<?php

use yii\db\Migration;

class m200326_123000_table_planfix_budget_cost_items extends Migration
{
    private $table = 'budget_cost_items';

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
