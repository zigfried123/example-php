<?php
namespace app\modules\budget\models;

/**
 * Class Payment system
 * @property int $id
 * @property int $key
 * @property string $date
 * @property string $name
 * @property boolean $is_deleted
 * @property string $created
 * @property string $updated
 *
 * @package app\modules\budget\models
 */
class ManagementPeriod extends BudgetBase
{
    public static $table = 'budget_management_periods';

    public function rules(): array
    {
        return array_merge([
            [['date', 'name', 'key'], 'required'],
            [['date', 'key'], 'unique'],
            ['date', 'datetime', 'format' => 'php:d-m-Y'],
            [['name',], 'string', 'max' => '255'],
            [['is_deleted'], 'boolean'],
            [['is_deleted'], 'safe'],

        ], parent::rules());
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID периода',
            'key' => 'Ключ',
            'date' => 'Дата',
            'name' => 'Наименование',
            'is_deleted' => 'Флаг удаления',
            'created' => 'Создана',
            'updated' => 'Изменена',
        ];
    }
}
