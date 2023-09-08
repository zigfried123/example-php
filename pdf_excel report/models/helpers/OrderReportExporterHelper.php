<?php
/**
 * Created by PhpStorm.
 * User: zigfried123
 * Date: 05.02.2018
 * Time: 16:50
 */

namespace app\modules\reports\models\orders\helpers;


class OrderReportExporterHelper
{

    public static function getYesOrNotArray($pos)
    {
        return [t('app','Not'),t('app','Yes')][$pos];
    }

}