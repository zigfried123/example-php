<?php

namespace app\modules\support\ar;

use app\modules\support\models\status\Type;
use DateTimeImmutable;
use Yii;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $action_id
 * @property int $task_id
 * @property int $task_num
 * @property bool $emergency
 * @property int $status
 * @property string $created
 */
class SupportTaskStatus extends ActiveRecord
{
    public static function getDb() {
        return Yii::$app->dbPlanfixSync;
    }

    public static function tableName(): string
    {
        return 'support_task_status';
    }

    /**
     * @return array[][]
     */
    public function rules(): array
    {
        return [
            [['action_id', 'task_id', 'task_num', 'emergency', 'status', 'created'], 'required'],
            [['action_id', 'task_id', 'task_num', 'status'], 'integer'],
            ['created', 'string']
        ];

    }

    public function getStatusType(): Type
    {
        return new Type($this->status);
    }

    public function getDateCreated(): DateTimeImmutable
    {
        return DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $this->created);
    }

    public function isCompleted(): bool
    {
        return $this->getStatusType()->isCompleted();
    }
}
