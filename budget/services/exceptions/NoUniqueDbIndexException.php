<?php

namespace app\modules\budget\services\exceptions;

use Exception;

class NoUniqueDbIndexException extends Exception
{
   public $message = 'No unique db index';
}
