<?php
/**
 * Created by PhpStorm.
 * User: zigfried123
 * Date: 23.04.2018
 * Time: 11:31
 */

namespace app\modules\v1\models\decorator;

class TagLastName
{
    private $obj;

    public function __construct($obj)
    {
        $this->obj = $obj;
    }

    public function replace($template,$data)
    {
        $template = str_replace('#LAST_NAME_RECIPIENT#',$data['lastName'],$template);
        return $this->obj->replace($template,$data);
    }
}