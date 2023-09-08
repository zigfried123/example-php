<?php

namespace app\modules\budget\models;

/**
 * Class BudgetItem
 * @property int $year
 * @property int $month
 * @property string $costItemCode
 * @property string $departmentCode
 * @property int $value
 * @property string $created
 * @property string $updated
 *
 * @package app\modules\budget\models
 */
class BudgetItem extends BudgetBase
{
    const EVENT_NEW_BUDGET_ITEM = 'new budget item';
    const EVENT_UPDATE_BUDGET_ITEM = 'update budget item';
    const EVENT_DELETE_BUDGET_ITEM = 'delete budget item';

    public static $table = 'budget_items';

    public function rules(): array
    {
        return array_merge([
            [['year', 'month', 'costItemCode', 'departmentCode', 'value'], 'required'],
            [['year', 'value'], 'number'],
            ['month', 'number', 'min' => 1, 'max' => 12],
            [['costItemCode', 'departmentCode'], 'string', 'max' => '255'],

        ], parent::rules());
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'year' => 'Год',
            'month' => 'Месяц',
            'сostItemCode' => 'Код статьи расходов',
            'departmentCode' => 'Код отдела',
            'value' => 'Сумма',
            'created' => 'Создан',
            'updated' => 'Изменен',
        ];
    }
}
