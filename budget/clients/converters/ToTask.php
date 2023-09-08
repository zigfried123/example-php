<?php

namespace app\modules\budget\clients\converters;

use app\modules\budget\clients\dto\task\Field;
use app\modules\budget\clients\dto\task\Task;

class ToTask
{
    /**
     * @param array $response
     * @return Task
     */
    public function convert(array $response): Task
    {
        $fields = [];

        foreach ($response['customData']['customValue'] as $item) {
            $value = $item['value'] ?: null;
            $text = $item['text'] ?: null;

            $fields[] = new Field(
                $item['field']['id'],
                $item['field']['name'],
                $value,
                $text
            );
        }

        $task = new Task(
            $response['id'],
            $response['title'],
            $response['description'],
            $response['importance'],
            $response['status'],
            $response['statusSet'],
            $response['checkResult'],
            $response['owner']['id'],
            $response['beginDateTime'],
            $response['general'],
            $response['isOverdued'],
            $response['isCloseToDeadline'],
            $response['isNotAcceptedInTime'],
            $fields
        );

        return $task;
    }
}
