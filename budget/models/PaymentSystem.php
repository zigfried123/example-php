<?php
namespace app\modules\budget\models;

/**
 * Class Payment system
 * @property int $id
 * @property string $name
 * @property boolean $is_deleted
 * @property string $created
 * @property string $updated
 *
 * @package app\modules\budget\models
 */
class PaymentSystem extends BudgetBase
{
    public static $table = 'budget_payment_systems';

    public function rules(): array
    {
        return array_merge([
            [['name'], 'required'],
            [['name'], 'string', 'max' => '255'],
            [['is_deleted'], 'boolean'],
            [['is_deleted'], 'safe'],

        ], parent::rules());
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID платежной системы',
            'name' => 'Наименование',
            'is_deleted' => 'Флаг удаления',
            'created' => 'Создана',
            'updated' => 'Изменена',
        ];
    }
}
