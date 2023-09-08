<?php

namespace app\modules\budget\commands;

use \yii\console\controllers\MigrateController as BaseMigrateController;

class MigrateController extends BaseMigrateController
{
    public $db = 'dbPlanfixSync';

    public $migrationPath = '@app/modules/budget/migrations';

    public $migrationTable = 'budget_migration';

    public $migrationNamespaces = [];
}
