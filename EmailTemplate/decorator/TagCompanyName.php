<?php
/**
 * Created by PhpStorm.
 * User: zigfried123
 * Date: 23.04.2018
 * Time: 11:34
 */

namespace app\modules\v1\models\decorator;

class TagCompanyName
{
    public function replace($template,$data)
    {
        $template = str_replace('#COMPANY_NAME#',$data['companyName'],$template);
        return $template;
    }
}