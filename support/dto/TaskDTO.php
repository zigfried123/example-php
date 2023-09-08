<?php

namespace app\modules\support\dto;

class TaskDTO
{
    /** @var int */
    private $taskId;

    /** @var int */
    private $numId;

    /** @var bool */
    private $emergency;

    /** @var string */
    private $department;

    /** @var string */
    private $serviceClass;

    /** @var string */
    private $supportService;

    /** @var string */
    private $tags;

    /** @var string  */
    private $taskName;

    public function __construct(int $taskId, int $numId, bool $emergency, string $department, string $serviceClass, string $supportService, string $tags, string $taskName)
    {
        $this->taskId = $taskId;
        $this->numId = $numId;
        $this->emergency = $emergency;
        $this->department = $department;
        $this->serviceClass = $serviceClass;
        $this->supportService = $supportService;
        $this->tags = $tags;
        $this->taskName = $taskName;
    }

    public function getId(): int
    {
        return $this->taskId;
    }

    public function getNumId(): int
    {
        return $this->numId;
    }

    public function isEmergency(): bool
    {
        return $this->emergency;
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

    /**
     * @return string
     */
    public function getTaskName(): string
    {
        return $this->taskName;
    }

}
