<?php

namespace app\modules\budget\providers;

use app\components\GoogleApiComponent;
use app\modules\budget\providers\contracts\BudgetProviderInterface;
use app\modules\budget\resources\ResourceBudgetItem;

class GoogleBudgetProvider implements BudgetProviderInterface
{
    /** @var GoogleApiComponent */
    private $googleApiComponent;

    public function __construct(GoogleApiComponent $googleApiComponent)
    {
        $this->googleApiComponent = $googleApiComponent;
    }

    /**
     * @return ResourceBudgetItem[]
     */
    public function getAll(): array
    {
        $sheetService = $this->googleApiComponent->getSheetsService();

        $spreadsheetId = env('GOOGLE_SPREADSHEET_ID');

        $spreadSheetService = $sheetService->spreadsheets->get($spreadsheetId);

        $sheets = $spreadSheetService->getSheets();

        $monthIndexes = [
            1 => 'январь',
            2 => 'февраль',
            3 => 'март',
            4 => 'апрель',
            5 => 'май',
            6 => 'июнь',
            7 => 'июль',
            8 => 'август',
            9 => 'сентябрь',
            10 => 'октябрь',
            11 => 'ноябрь',
            12 => 'декабрь'
        ];

        /** @var \Google_Service_Sheets_Sheet $sheet */
        foreach ($sheets as $sheet) {
            $properties = $sheet->getProperties();

            $departmentCodeFull = $properties->getTitle();

            preg_match('/(?P<code>\d+/\d+)/', $departmentCodeFull, $matches);

            $departmentCodePart = $matches['code'];

            $valueRangeService = $sheetService->spreadsheets_values->get($spreadsheetId, $departmentCodeFull);

            $allValues = $valueRangeService->getValues();
            $cols = current(array_splice($allValues, 0, 1));
            $rows = array_splice($allValues, 0);

            $dates = array_splice($cols, 2);

            foreach ($rows as $row) {

                list($costItemCode, $costItemName) = $row;

                $dateValues = array_splice($row, 2);

                foreach ($dates as $key => $date) {

                    list($year, $month) = explode(' ', $date);

                    $month = array_search($month, $monthIndexes);

                    $value = $dateValues[$key];

                    $value = intval(preg_replace("/[^x\d|*\.]/", '', $value));

                    $items[] = new ResourceBudgetItem($year, $month, $costItemCode, $departmentCodePart, $value);
                }

            }

            sleep(1);
        }

        return $items;
    }

}
