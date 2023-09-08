<?php

use yii\db\Migration;

/**
 * Class m200401_132351_add_table_budget_item
 */
class m200401_132351_add_table_budget_item extends Migration
{
    private $table = 'budget_items';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->table, [
            'id' => $this->primaryKey(),
            'year' => $this->integer(),
            'month' => $this->integer(),
            'costItemCode' => $this->string(255),
            'departmentCode' => $this->string(255),
            'value' => $this->integer(),
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
