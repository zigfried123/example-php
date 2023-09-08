<?php

use yii\db\Migration;

/**
 * Class m200402_094228_add_unique_index_to_items
 */
class m200402_094228_add_unique_index_to_items extends Migration
{
    const INDEX = 'costItemCode-month-departmentCode-year-UX';

    private $table = 'budget_items';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex(self::INDEX, $this->table, ['costItemCode', 'month', 'departmentCode', 'year'], true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
       $this->dropIndex(self::INDEX, $this->table);
    }

}
