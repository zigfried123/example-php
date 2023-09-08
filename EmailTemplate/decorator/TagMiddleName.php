<?php
/**
 * Created by PhpStorm.
 * User: zigfried123
 * Date: 23.04.2018
 * Time: 11:33
 */

namespace app\modules\v1\models\decorator;

class TagMiddleName
{
    private $obj;

    public function __construct($obj)
    {
        $this->obj = $obj;
    }

    public function replace($template,$data)
    {
        $template = str_replace('#MIDDLE_NAME_RECIPIENT#',$data['middleName'],$template);
        return $this->obj->replace($template,$data);
    }
}