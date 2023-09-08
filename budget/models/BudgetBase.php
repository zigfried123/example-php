<?php
namespace app\modules\budget\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Connection;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

class BudgetBase extends ActiveRecord
{
    public static $db = 'dbPlanfixSync';
    public static $table;

    /**
     * tableName
     * @return string
     */
    public static function tableName(): string
    {
        return static::$table;
    }

    /**
     * @return Connection
     */
    public static function getDb(): Connection
    {
        return Yii::$app->dbPlanfixSync;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        $data = parent::__get($name);
        if ($data instanceof self) {
            return $data->setBaseIds($this);
        }

        return $data;
    }

    public function rules(): array
    {
        return array_merge([
            [['created', 'updated'], 'date', 'format' => 'yyyy-M-d H:m:s'],
            [['created', 'updated'], 'safe'],

        ], parent::rules());
    }

    public function behaviors(): array
    {
        return ArrayHelper::merge(
            parent::behaviors(),
            [
                'timestamp' => [
                    'class' => TimestampBehavior::class,
                    'value' => new Expression('NOW()'),
                    'createdAtAttribute' => 'created',
                    'updatedAtAttribute' => 'updated',
                ],
            ]
        );
    }
}