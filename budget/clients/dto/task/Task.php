<?php

namespace app\modules\budget\clients\dto\task;

class Task
{
    /**
     * @var int - идентификатор задачи
     */
    private $id;

    /**
     * @var string - название
     */
    private $title;

    /**
     * @var string - описание
     */
    private $description;

    /**
     * @var string - срочность AVERAGE/HIGH
     */
    private $importance;

    /**
     * @var int - идентификатор статуса
     */
    private $status;

    /**
     * @var int - идентификатор процесса задачи
     */
    private $statusSet;

    /**
     * @var bool - является ли задача задачей с обязательной проверкой результата
     */
    private $checkResult;

    /**
     * @var int - создатель задачи
     */
    private $ownerId;

    /**
     * @var string - время создания задачи дд-мм-гггг чч:мм
     */
    private $beginDateTime;

    /**
     * @var int - номер задачи
     */
    private $general;

    /**
     * @var bool - задача не выполнена в срок
     */
    private $isOverdued;

    /**
     * @var bool - задача близка к дедлайну
     */
    private $isCloseToDeadline;

    /**
     * @var bool - задача не принята вовремя
     */
    private $isNotAcceptedInTime;

    /** @var Field[] */
    private $customFields;

    /**
     * Task constructor.
     * @param int $id
     * @param string $title
     * @param string $description
     * @param string $importance
     * @param int $status
     * @param int $statusSet
     * @param bool $checkResult
     * @param int $ownerId
     * @param string $beginDateTime
     * @param int $general
     * @param bool $isOverdued
     * @param bool $isCloseToDeadline
     * @param bool $isNotAcceptedInTime
     * @param Field[] $customFields
     */
    public function __construct(
        int $id,
        string $title,
        string $description,
        string $importance,
        int $status,
        int $statusSet,
        bool $checkResult,
        int $ownerId,
        string $beginDateTime,
        int $general,
        bool $isOverdued,
        bool $isCloseToDeadline,
        bool $isNotAcceptedInTime,
        array $customFields
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->importance = $importance;
        $this->status = $status;
        $this->statusSet = $statusSet;
        $this->checkResult = $checkResult;
        $this->ownerId = $ownerId;
        $this->beginDateTime = $beginDateTime;
        $this->general = $general;
        $this->isOverdued = $isOverdued;
        $this->isCloseToDeadline = $isCloseToDeadline;
        $this->isNotAcceptedInTime = $isNotAcceptedInTime;
        $this->customFields = $customFields;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getImportance(): string
    {
        return $this->importance;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getStatusSet(): int
    {
        return $this->statusSet;
    }

    public function isCheckResult(): bool
    {
        return $this->checkResult;
    }

    public function getOwnerId(): int
    {
        return $this->ownerId;
    }

    public function getBeginDateTime(): string
    {
        return $this->beginDateTime;
    }

    public function getGeneral(): int
    {
        return $this->general;
    }

    public function isOverdued(): bool
    {
        return $this->isOverdued;
    }

    public function isCloseToDeadline(): bool
    {
        return $this->isCloseToDeadline;
    }

    public function isNotAcceptedInTime(): bool
    {
        return $this->isNotAcceptedInTime;
    }

    /**
     * @return Field[]
     */
    public function getCustomFields(): array
    {
        return $this->customFields;
    }
}
