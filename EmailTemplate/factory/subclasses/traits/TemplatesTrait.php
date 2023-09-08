<?php

namespace app\modules\v1\models\factory\subclasses\traits;

use app\modules\v1\models\BindTemplateToEmail;
use app\modules\v1\models\BodyGenerator;

/**
 * Created by PhpStorm.
 * User: zigfried123
 * Date: 17.04.2018
 * Time: 13:20
 */
trait TemplatesTrait
{
    private $lastName;
    private $secondName;
    private $companyName;
    private $tenantId;
    private $type;
    private $lang;
    private static $userTemplates = ['confirmUserEmail','passwordResetToken','chatMessage'];

    public function getBody(array $queryParams)
    {
        foreach ($queryParams as $key => $val) {
            $this->$key = $val;
        }

        $this->tenantId    = $queryParams['tenant_id'];
        $this->lastName    = $queryParams['last_name'];
        $this->secondName  = $queryParams['second_name'];
        $this->companyName = $queryParams['company_name'];

        $content = $this->getContent();
        $data    = [
            'lastName'    => $this->lastName,
            'name'        => $this->name,
            'secondName'  => $this->secondName,
            'companyName' => $this->companyName,
            'tenantId'    => $this->tenantId,
        ];

        $setting = in_array($this->type,self::$userTemplates) ? 'user' : 'default';
        $lang = $this->lang;

        return (new BodyGenerator(compact('data', 'content','setting','lang')))->execute();
    }

}