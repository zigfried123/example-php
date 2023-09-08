<?php
/**
 * Created by PhpStorm.
 * User: zigfried123
 * Date: 23.04.2018
 * Time: 11:10
 */

namespace app\modules\v1\models\decorator;

class TagContent
{
    private $obj;

    public function __construct($obj)
    {
        $this->obj = $obj;
    }

    public function replace($template,$data)
    {
        $template = str_replace('#CONTENT#',$data['content'],$template);
        return $this->obj->replace($template,$data);
    }

}