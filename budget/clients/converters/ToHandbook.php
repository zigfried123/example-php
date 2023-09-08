<?php

namespace app\modules\budget\clients\converters;

use app\modules\budget\clients\dto\handbook\Field;
use app\modules\budget\clients\dto\handbook\Record;

class ToHandbook
{
    /**
     * @param array $response
     * @return Record[]
     */
    public function convert(array $response): array
    {
        $records = [];

        $recordsArr = $response['record'];

        if ($recordsArr['key']) {
            $recordsArr = [$recordsArr];
        }

        foreach ($recordsArr as $record) {

            $fields = [];

            if (!$record['isGroup']) {

                $customValue = $record['customData']['customValue'];

                if ($customValue['field']) {
                    $customValue = [$customValue];
                }

                foreach ($customValue as $arrField) {
                    $value = is_string($arrField['value']) ? $arrField['value'] : '';
                    $text = is_string($arrField['text']) ? $arrField['value'] : '';

                    $fields[] = new Field($arrField['field']['id'], $value, $text);
                }
            }

            $records[] = new Record($record['parentKey'], $record['isGroup'], $record['key'], $fields);
        }

        return $records;
    }
}
