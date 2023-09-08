<?php
namespace app\modules\budget\models;

/**
 * Class BudgetDepartment
 * @property int $id
 * @property int $key
 * @property string $code
 * @property string $name
 * @property boolean $is_deleted
 * @property string $created
 * @property string $updated
 *
 * @package app\modules\budget\models
 */
class BudgetDepartment extends BudgetBase
{
    public static $table = 'budget_departments';

    public function rules(): array
    {
        return array_merge([
            [['code', 'name', 'key'], 'required'],
            [['code', 'key'], 'unique'],
            [['code', 'name',], 'string', 'max' => '255'],
            [['is_deleted'], 'boolean'],
            [['is_deleted'], 'safe'],

        ], parent::rules());
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID департамента',
            'key' => 'Ключ',
            'code' => 'Код департамента',
            'name' => 'Наименование',
            'is_deleted' => 'Флаг удаления',
            'created' => 'Создан',
            'updated' => 'Изменен',
        ];
    }
}
