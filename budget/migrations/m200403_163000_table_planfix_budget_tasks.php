<?php

use yii\db\Migration;

class m200403_163000_table_planfix_budget_tasks extends Migration
{
    private $table = 'budget_tasks';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(
            $this->table,
            [
                'id' => $this->primaryKey(),
                'task_id' => $this->integer(),
                'status' => $this->integer(),
                'owner_id' => $this->integer(),
                'task_created' => $this->dateTime(),
                'general' => $this->integer(),
                'department' => $this->integer(),
                'cost_item' => $this->integer(),
                'currency' => $this->integer(),
                'management_period' => $this->integer(),
                'payment_system' => $this->integer(),
                'project' => $this->integer(),
                'amount_paid' => $this->float(),
                'amount_in_currency' => $this->float(),
                'amount_in_rub' => $this->float(),
                'created' => $this->dateTime(),
                'updated' => $this->dateTime(),
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->table);
    }
}
