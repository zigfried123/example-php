<?php

namespace app\modules\budget\models;

/**
 * @property int $id
 * @property int $task_id
 * @property int $status
 * @property int $owner_id
 * @property string $task_created
 * @property int $general
 * @property int $department
 * @property int|null $cost_item
 * @property int $currency
 * @property int|null $management_period
 * @property int $payment_system
 * @property int|null $project
 * @property int $amount_paid
 * @property int $amount_in_currency
 * @property int $amount_in_rub
 * @property int $created
 * @property int $updated
 */
class Task extends BudgetBase
{
    const EVENT_NEW_TASK = 'event_new_task';
    const EVENT_CHANGE_TASK = 'event_change_task';
    const EVENT_DELETE_TASK = 'event_delete_task';

    public static $table = 'budget_tasks';

    public function rules(): array
    {
        return array_merge([
            [
                [
                    'task_id',
                    'status',
                    'owner_id',
                    'task_created',
                    'general',
                    'department',
                    'currency',
                    'payment_system',
                    'amount_paid',
                    'amount_in_currency',
                    'amount_in_rub',
                ],
                'required'
            ],
            [['task_id'], 'unique'],
            [
                [
                    'task_id',
                    'status',
                    'owner_id',
                    'general',
                    'department',
                    'currency',
                    'management_period',
                    'payment_system',
                ],
                'integer'
            ],
            [
                [
                    'amount_in_currency',
                    'amount_in_rub',
                ],
                'double'
            ]


        ], parent::rules());
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID проекта',
            'task_id' => 'ID задачи',
            'status' => 'Статус',
            'owner_id' => 'Владелец',
            'task_created' => 'Дата создания задачи',
            'general' => 'Номер задачи',
            'department' => 'Отдел контролирующий бюджет',
            'cost_item' => 'Статья затрат',
            'currency' => 'Валюта',
            'management_period' => 'Управленченский период',
            'payment_system' => 'Платежная система',
            'project' => 'Проект для аналитики',
            'amount_paid' => 'Оплаченная сумма',
            'amount_in_currency' => 'Сумма в валюте',
            'amount_in_rub' => 'Сумма в рублях',
            'created' => 'Создана',
            'updated' => 'Изменена',
        ];
    }
}
