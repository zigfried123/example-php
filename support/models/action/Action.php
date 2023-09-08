<?php

namespace app\modules\support\models\action;

use DateTime;

class Action
{
    /** @var int */
    private $id;

    /** @var Type */
    private $type;

    /** @var string */
    private $description;

    /** @var DateTime */
    private $date;

    public function __construct(int $id, Type $type, string $description, DateTime $date)
    {
        $this->id = $id;
        $this->type = $type;
        $this->description = $description;
        $this->date = $date;
    }

    public static function getInstance(array $action): Action
    {
        $description = is_string($action['description']) ? $action['description'] : json_encode($action['description']);

        return new Action(
            $action['id'],
            new Type($action['type']),
            $description,
            DateTime::createFromFormat('d-m-Y H:i', $action['dateTime'])
        );
    }

    public function isActionChangeStatus(): bool
    {
        return $this->type->isTaskCreated() ||
            $this->type->isStatusChange() ||
            ($this->type->isTaskChanged() && $this->isDescriptionContainsChangeEmergency());
    }

    public function getType(): Type
    {
        return $this->type;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDate(): DateTime
    {
        return $this->date;
    }

    public function isEmergency(): bool
    {
        $lowerDescription = mb_strtolower($this->description);

        return $this->isDescriptionContainsChangeEmergency() &&
            strpos($lowerDescription, 'срочная') < strpos($lowerDescription, 'обычная');
    }

    private function isDescriptionContainsChangeEmergency(): bool
    {
        $lowerDescription = mb_strtolower($this->description);

        return strpos($lowerDescription, 'срочная') !== false && strpos($lowerDescription, 'обычная') !== false;
    }
}
