<?php

namespace app\modules\support\models\action;

class Type
{
    const TASK_CREATED = 'TASKCREATED';
    const STATUS_CHANGED = 'STATUSCHANGED';
    const TASK_ACCEPTED = 'TASKACCEPTED';
    const TASK_CHANGE = 'TASKCHANGED';

    const TYPES = [
        self::TASK_CREATED,
        self::STATUS_CHANGED,
        self::TASK_CHANGE,
    ];

    /** @var string */
    private $code;

    public function __construct(string $code)
    {
        $this->code = $code;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function isTaskCreated(): bool
    {
        return $this->code === self::TASK_CREATED;
    }

    public function isTaskChanged(): bool
    {
        return $this->code === self::TASK_CHANGE;
    }

    public function isStatusChange(): bool
    {
        return $this->code === self::STATUS_CHANGED || $this->code === self::TASK_ACCEPTED;
    }
}