<?php

use yii\db\Migration;

class m200402_194300_add_key_field extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('budget_cost_items', 'key', $this->integer()->after('id'));
        $this->addColumn('budget_currencies', 'key', $this->integer()->after('id'));
        $this->addColumn('budget_departments', 'key', $this->integer()->after('id'));
        $this->addColumn('budget_management_periods', 'key', $this->integer()->after('id'));
        $this->addColumn('budget_payment_systems', 'key', $this->integer()->after('id'));
        $this->addColumn('budget_projects', 'key', $this->integer()->after('id'));

        $this->createIndex('uniq-key', 'budget_cost_items', 'key', true);
        $this->createIndex('uniq-key', 'budget_currencies', 'key', true);
        $this->createIndex('uniq-key', 'budget_departments', 'key', true);
        $this->createIndex('uniq-key', 'budget_management_periods', 'key', true);
        $this->createIndex('uniq-key', 'budget_payment_systems', 'key', true);
        $this->createIndex('uniq-key', 'budget_projects', 'key', true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('budget_cost_items', 'key');
        $this->dropColumn('budget_currencies', 'key');
        $this->dropColumn('budget_departments', 'key');
        $this->dropColumn('budget_management_periods', 'key');
        $this->dropColumn('budget_payment_systems', 'key');
        $this->dropColumn('budget_projects', 'key');

        $this->dropIndex('uniq-key', 'budget_cost_items');
        $this->dropIndex('uniq-key', 'budget_currencies');
        $this->dropIndex('uniq-key', 'budget_departments');
        $this->dropIndex('uniq-key', 'budget_management_periods');
        $this->dropIndex('uniq-key', 'budget_payment_systems');
        $this->dropIndex('uniq-key', 'budget_projects');
    }
}
