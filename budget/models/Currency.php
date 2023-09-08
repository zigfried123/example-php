<?php
namespace app\modules\budget\models;

/**
 * Class Currency
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
class Currency extends BudgetBase
{
    public static $table = 'budget_currencies';

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
            'id' => 'ID валюты',
            'key' => 'Ключ',
            'code' => 'Код валюты',
            'name' => 'Наименование',
            'is_deleted' => 'Флаг удаления',
            'created' => 'Создана',
            'updated' => 'Изменена',
        ];
    }
}
