<?php

namespace app\modules\support\models\status;

use DateTime;

class Status
{
    /** @var int */
    private $actionId;

    /** @var Type */
    private $type;

    /** @var int */
    private $taskId;

    /** @var int */
    private $taskNum;

    /** @var bool */
    private $emergency;

    /** @var DateTime */
    private $datetime;

    /** @var string */
    private $department;

    /** @var string */
    private $serviceClass;

    /** @var string */
    private $supportService;

    /** @var string */
    private $tags;

    /** @var string */
    private $taskName;

    public function __construct(int $actionId, Type $type, int $taskId, int $taskNum, bool $emergency, DateTime $datetime, string $department, string $serviceClass, string $supportService, string $tags, string $taskName)
    {
        $this->actionId = $actionId;
        $this->type = $type;
        $this->taskId = $taskId;
        $this->taskNum = $taskNum;
        $this->emergency = $emergency;
        $this->datetime = $datetime;

        $this->department = $department;
        $this->serviceClass = $serviceClass;
        $this->supportService = $supportService;
        $this->tags = $tags;
        $this->taskName = $taskName;
    }

    /**
     * @return string
     */
    public function getDepartment(): string
    {
        return $this->department;
    }

    /**
     * @return string
     */
    public function getServiceClass(): string
    {
        return $this->serviceClass;
    }

    /**
     * @return string
     */
    public function getSupportService(): string
    {
        return $this->supportService;
    }

    /**
     * @return string
     */
    public function getTags(): string
    {
        return $this->tags;
    }

    public function getActionId(): int
    {
        return $this->actionId;
    }

    public function getType(): Type
    {
        return $this->type;
    }

    public function getTaskId(): int
    {
        return $this->taskId;
    }

    public function getTaskNum(): int
    {
        return $this->taskNum;
    }

    public function isEmergency(): bool
    {
        return $this->emergency;
    }

    public function getDatetime(): DateTime
    {
        return $this->datetime;
    }

    public function getTaskName(): string
    {
        return $this->taskName;
    }
}
